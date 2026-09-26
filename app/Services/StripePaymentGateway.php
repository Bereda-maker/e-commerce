<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use Stripe\StripeClient;

class StripePaymentGateway implements PaymentGateway
{
    public function __construct(private readonly StripeClient $stripe) {}

    public function createPaymentIntent(int $amountCents, string $currency, array $metadata): object
    {
        return $this->stripe->paymentIntents->create([
            'amount' => $amountCents,
            'currency' => $currency,
            'metadata' => $metadata,
        ]);
    }

    public function cancelPaymentIntent(string $paymentIntentId): void
    {
        try {
            $this->stripe->paymentIntents->cancel($paymentIntentId);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Already succeeded, already canceled, or otherwise not in a
            // cancelable state on Stripe's side — our own status check in
            // CheckoutService::cancelOrder() already guards against the
            // common case (a payment that succeeded between the buyer
            // loading the order page and clicking cancel); this catch is
            // a last-resort no-op rather than surfacing a 500 to a buyer
            // who correctly clicked "cancel" on a pending order.
        }
    }
}
