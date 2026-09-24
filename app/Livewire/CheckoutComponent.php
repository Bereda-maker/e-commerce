<?php

namespace App\Livewire;

use App\Exceptions\OutOfStockException;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * A guided, single-focus multi-step flow (review -> pay -> confirmation)
 * rather than one long form. Card details are collected entirely by
 * Stripe Elements in the Blade view's JavaScript, client-side — this
 * component never receives or touches raw card data at any step, only a
 * PaymentIntent id/client_secret that Stripe itself issued.
 */
class CheckoutComponent extends Component
{
    public string $step = 'review'; // review -> paying -> confirmation | failed

    public ?int $orderId = null;

    public ?string $clientSecret = null;

    public ?string $errorMessage = null;

    public function placeOrder(CheckoutService $checkout): void
    {
        $user = Auth::user();
        $cart = $user->cart()->with('items.variant.product')->first();

        if (! $cart || $cart->isEmpty()) {
            $this->errorMessage = 'Your cart is empty.';
            return;
        }

        try {
            $order = $checkout->checkout($user, $cart);
            $this->orderId = $order->id;
            $this->clientSecret = $order->client_secret;
            $this->step = 'paying'; // hand off to Stripe Elements in the view
        } catch (OutOfStockException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    /** Called by the browser once Stripe confirms the card was charged client-side. */
    public function paymentConfirmed(): void
    {
        // The order's status flips to "paid" via the Stripe webhook
        // (StripeWebhookController), not here — this client-side callback
        // only moves the UI forward. Trusting a browser event to mark an
        // order paid would let anyone fake a payment by calling this
        // method directly; the webhook, verified by signature, is the
        // only source of truth for payment state. See README "Security".
        $this->step = 'confirmation';
    }

    public function paymentFailed(string $message): void
    {
        $this->errorMessage = $message;
        $this->step = 'failed';
    }

    public function render()
    {
        $cart = Auth::user()->cart()->with('items.variant.product')->first();

        return view('livewire.checkout', ['cart' => $cart]);
    }
}
