<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Services\StripePaymentGateway;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StripeClient::class, fn () => new StripeClient(config('services.stripe.secret')));

        $this->app->bind(PaymentGateway::class, StripePaymentGateway::class);
    }

    public function boot(): void
    {
        // Belt-and-suspenders alongside trustProxies() in bootstrap/app.php:
        // even if proxy-trust detection ever misfires for a given request,
        // every URL Laravel generates in production is forced to https://
        // outright, so a page can never render an insecure form action.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
