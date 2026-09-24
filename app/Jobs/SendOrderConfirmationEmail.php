<?php

namespace App\Jobs;

use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Dispatched from CheckoutService::handlePaymentSucceeded(), which runs
 * inside the webhook request. Sending mail synchronously there would make
 * a slow mail provider stall Stripe's webhook response — Stripe retries
 * webhooks that don't respond quickly, which could reprocess the same
 * event repeatedly. Queuing it keeps the webhook response fast and lets
 * this run on the separate long-lived queue worker process. See
 * DEPLOYMENT.md "Queue worker".
 */
class SendOrderConfirmationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly Order $order) {}

    public function handle(): void
    {
        $this->order->loadMissing('items', 'user');

        Mail::to($this->order->user->email)->send(new OrderConfirmationMail($this->order));
    }
}
