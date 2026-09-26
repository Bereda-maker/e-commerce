<?php

use App\Contracts\PaymentGateway;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\DB;

/**
 * A minimal fake satisfying the PaymentGateway interface (see
 * app/Contracts/PaymentGateway.php) — no real Stripe network call, and no
 * need to mock the Stripe SDK's own magic-property internals. This is the
 * whole reason CheckoutService depends on an interface rather than the
 * concrete Stripe\StripeClient class.
 */
final class FakePaymentGateway implements PaymentGateway
{
    public function createPaymentIntent(int $amountCents, string $currency, array $metadata): object
    {
        return (object) [
            'id' => 'pi_test_'.bin2hex(random_bytes(8)),
            'client_secret' => 'secret_test',
        ];
    }

    public function cancelPaymentIntent(string $paymentIntentId): void
    {
        // no-op — no real Stripe call to cancel in tests
    }
}

/**
 * This is the whole lesson of the project made concrete: two genuinely
 * simultaneous checkout attempts for the same last unit of stock, and an
 * assertion that exactly one succeeds.
 *
 * "Simultaneous" has to mean genuinely concurrent database transactions,
 * not two sequential calls in one PHP process (which would never actually
 * race, since PHP is single-threaded and the second call would just see
 * whatever the first one already committed). This test uses pcntl_fork()
 * to run each checkout attempt in its own OS process with its own MySQL
 * connection — the same reason the Node/Bun cross-instance test in
 * Project 4 spawned two real server processes rather than two sockets on
 * one process.
 *
 * Requires the pcntl extension (present on Linux by default; this is why
 * CI runs on ubuntu-latest — see .github/workflows/ci.yml).
 */
beforeEach(function () {
    if (! extension_loaded('pcntl')) {
        $this->markTestSkipped('pcntl extension not available — required to genuinely parallelize two checkout transactions.');
    }
});

test('exactly one of two simultaneous checkouts succeeds for the last unit of stock', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->for($product)->lastUnit()->create(); // stock_count = 1

    $buyerA = User::factory()->create();
    $buyerB = User::factory()->create();

    foreach ([$buyerA, $buyerB] as $buyer) {
        $cart = Cart::factory()->for($buyer)->create();
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);
    }

    // Disconnect before forking — a PDO connection must not be shared
    // across forked processes; each child reconnects independently.
    DB::disconnect();

    $resultFileA = tempnam(sys_get_temp_dir(), 'checkout_a_');
    $resultFileB = tempnam(sys_get_temp_dir(), 'checkout_b_');

    $pidA = pcntl_fork();
    if ($pidA === 0) {
        // Child A
        runCheckoutAttempt($buyerA->id, $resultFileA);
        exit(0);
    }

    $pidB = pcntl_fork();
    if ($pidB === 0) {
        // Child B
        runCheckoutAttempt($buyerB->id, $resultFileB);
        exit(0);
    }

    // Parent: wait for both children to finish.
    pcntl_waitpid($pidA, $statusA);
    pcntl_waitpid($pidB, $statusB);

    $outcomeA = trim(file_get_contents($resultFileA));
    $outcomeB = trim(file_get_contents($resultFileB));
    @unlink($resultFileA);
    @unlink($resultFileB);

    $outcomes = [$outcomeA, $outcomeB];
    $succeeded = array_filter($outcomes, fn ($o) => $o === 'success');
    $outOfStock = array_filter($outcomes, fn ($o) => $o === 'out_of_stock');

    // The actual assertion the whole test exists to make: exactly one
    // buyer got the unit, exactly one was correctly told it's sold out —
    // not two successes (overselling) and not two failures (a bug that
    // would incorrectly reject a legitimate sole buyer).
    expect($succeeded)->toHaveCount(1);
    expect($outOfStock)->toHaveCount(1);

    $variant->refresh();
    expect($variant->stock_count)->toBe(0);
});

/**
 * Runs inside a forked child process: reconnects to the DB fresh, performs
 * one checkout attempt, and writes the outcome to a file so the parent
 * process (which has no shared memory with the child) can read it back.
 */
function runCheckoutAttempt(int $buyerId, string $resultFile): void
{
    DB::reconnect();

    $buyer = User::find($buyerId);
    $cart = $buyer->cart()->with('items')->first();

    $checkout = new CheckoutService(new FakePaymentGateway());

    try {
        $checkout->checkout($buyer, $cart);
        file_put_contents($resultFile, 'success');
    } catch (\App\Exceptions\OutOfStockException $e) {
        file_put_contents($resultFile, 'out_of_stock');
    } catch (\Throwable $e) {
        file_put_contents($resultFile, 'error: '.$e->getMessage());
    }
}
