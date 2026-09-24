<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::middleware('auth')->group(function () {
    Route::get('/cart', fn () => view('cart'))->name('cart');
    Route::get('/checkout', fn () => view('checkout'))->name('checkout');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::get('/profile', function () {
        return view('profile.edit');
    })->name('profile.edit');
});

// Stripe posts here directly — excluded from CSRF verification (see
// bootstrap/app.php) and verified instead by its own signature check
// inside the controller. Never add the 'auth' or 'web' session middleware
// here; Stripe's server has no session/CSRF token to present.
Route::post('/webhooks/stripe', StripeWebhookController::class)->name('webhooks.stripe');

require __DIR__.'/auth.php';
