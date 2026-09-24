<?php

namespace App\Contracts;

/**
 * The one Stripe operation CheckoutService needs, behind an interface.
 * This keeps the row-locking transaction logic testable without a real
 * network call to Stripe, and without the test needing to fake or mock
 * the concrete Stripe SDK class (which has its own magic-property
 * internals that don't mock cleanly) — see FakePaymentGateway in
 * tests/Feature/CheckoutConcurrencyTest.php.
 */
interface PaymentGateway
{
    /** @return object{id: string, client_secret: string} */
    public function createPaymentIntent(int $amountCents, string $currency, array $metadata): object;
}
