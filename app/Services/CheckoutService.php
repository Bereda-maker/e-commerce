<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Exceptions\OutOfStockException;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Stripe\PaymentIntent;

class CheckoutService
{
    public function __construct(private readonly PaymentGateway $paymentGateway) {}

    /**
     * The whole lesson of this project: lockForUpdate() locks each variant
     * row for the duration of the transaction, so a second, simultaneous
     * checkout attempting to buy the same last unit has to wait its turn
     * and sees the *post-decrement* stock count — not a stale read from a
     * moment earlier. Without this line, two requests can both read
     * stock_count = 1, both decide "there's enough," and both succeed,
     * selling the same unit twice. See tests/Feature/CheckoutConcurrencyTest.php,
     * which asserts exactly one of two simultaneous checkouts succeeds.
     *
     * Inventory is decremented here, synchronously, at order-creation time
     * — not deferred until Stripe's webhook confirms payment. That's
     * deliberate: reserving stock as soon as the order is placed (as
     * "pending") is what prevents overselling between "customer clicked
     * buy" and "Stripe confirms the charge" a few hundred milliseconds
     * later. If the payment then fails, handlePaymentFailed() below
     * releases the reservation back to stock.
     *
     * @throws OutOfStockException
     */
    public function checkout(User $user, Cart $cart): Order
    {
        return DB::transaction(function () use ($user, $cart) {
            $totalCents = 0;
            $orderItemsData = [];

            foreach ($cart->items as $item) {
                /** @var ProductVariant $variant */
                $variant = ProductVariant::where('id', $item->product_variant_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($variant->stock_count < $item->quantity) {
                    throw new OutOfStockException($variant, $item->quantity);
                }

                $variant->decrement('stock_count', $item->quantity);

                $lineTotal = $variant->price_cents * $item->quantity;
                $totalCents += $lineTotal;

                $orderItemsData[] = [
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_label' => $variant->label(),
                    'unit_price_cents' => $variant->price_cents,
                    'quantity' => $item->quantity,
                ];
            }

            $order = Order::create([
                'user_id' => $user->id,
                'status' => Order::STATUS_PENDING,
                'total_cents' => $totalCents,
            ]);

            foreach ($orderItemsData as $data) {
                $order->items()->create($data);
            }

            // PaymentIntent creation happens inside the same DB transaction
            // as the stock decrement/order creation. If Stripe's API call
            // fails, the whole transaction (including the stock decrement)
            // rolls back — the reservation is never made without a
            // corresponding payment attempt in flight.
            $paymentIntent = $this->paymentGateway->createPaymentIntent(
                $totalCents,
                'usd',
                ['order_id' => $order->id]
            );

            $order->update(['stripe_payment_intent_id' => $paymentIntent->id]);
            $order->setAttribute('client_secret', $paymentIntent->client_secret);

            $cart->items()->delete();

            return $order;
        });
    }

    /** Called from the Stripe webhook once payment is confirmed. */
    public function handlePaymentSucceeded(PaymentIntent $paymentIntent): void
    {
        $order = Order::where('stripe_payment_intent_id', $paymentIntent->id)->first();
        if (! $order || $order->status !== Order::STATUS_PENDING) {
            return; // already processed, or not one of our orders — ignore idempotently
        }

        $order->update(['status' => Order::STATUS_PAID]);

        \App\Jobs\SendOrderConfirmationEmail::dispatch($order);
    }

    /**
     * A failed payment releases the reserved inventory back to stock —
     * the buyer sees a clear retry path rather than the item silently
     * staying "sold" while no payment was ever taken.
     */
    public function handlePaymentFailed(PaymentIntent $paymentIntent): void
    {
        DB::transaction(function () use ($paymentIntent) {
            $order = Order::where('stripe_payment_intent_id', $paymentIntent->id)
                ->lockForUpdate()
                ->first();

            if (! $order || $order->status !== Order::STATUS_PENDING) {
                return;
            }

            foreach ($order->items as $item) {
                ProductVariant::where('id', $item->product_variant_id)
                    ->lockForUpdate()
                    ->increment('stock_count', $item->quantity);
            }

            $order->update(['status' => Order::STATUS_PAYMENT_FAILED]);
        });
    }
}
