<?php

namespace App\Http\Controllers;

use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

/**
 * Stripe confirms payment asynchronously here — deliberately kept separate
 * from the synchronous checkout request in CheckoutComponent. The browser
 * telling us "payment succeeded" is never trusted; only a correctly-signed
 * event from Stripe itself moves an order to "paid". See README "Security".
 *
 * This route must be excluded from CSRF verification (it's an external
 * POST from Stripe, not a form on our own site) — see bootstrap/app.php /
 * routes/web.php where it's registered outside the default web middleware
 * group's CSRF check, verified instead by the Stripe-Signature header.
 */
class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, CheckoutService $checkout): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature', '');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $signature, $webhookSecret);
        } catch (SignatureVerificationException $e) {
            // A forged request (no valid signature) is rejected outright —
            // this is the check that stops anyone from POSTing a fake
            // "payment succeeded" event directly to this endpoint.
            Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);
            return response('invalid signature', 400);
        } catch (\UnexpectedValueException $e) {
            return response('invalid payload', 400);
        }

        match ($event->type) {
            'payment_intent.succeeded' => $checkout->handlePaymentSucceeded($event->data->object),
            'payment_intent.payment_failed' => $checkout->handlePaymentFailed($event->data->object),
            default => null, // ignore event types we don't act on
        };

        return response('ok', 200);
    }
}
