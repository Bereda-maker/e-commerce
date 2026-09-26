<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Stripe's webhook POST carries no CSRF token (it's not a form
        // submission from our own site) — verified instead by its own
        // signature check inside StripeWebhookController. CSRF protection
        // stays on by default in Laravel for every other route, which is
        // the point this exclusion is deliberately narrow.
        $middleware->validateCsrfTokens(except: ['webhooks/stripe']);

        $middleware->alias(['admin' => \App\Http\Middleware\EnsureUserIsAdmin::class]);

        // Render (like Heroku, Railway, and most PaaS hosts) terminates
        // HTTPS at its own edge and forwards plain HTTP to this
        // container. Without trusting that proxy, Laravel has no way to
        // know the original request was secure — it reads the raw
        // connection it actually received (HTTP) and generates insecure
        // http:// URLs for every form action, redirect, and asset,
        // exactly the "not secure" warning a browser shows on submit.
        // '*' trusts whichever single proxy hop delivers the request,
        // appropriate here since Render's own edge is that one hop.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
