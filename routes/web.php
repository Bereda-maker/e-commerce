<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\FavoriteController;
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
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

    Route::get('/profile', function () {
        return view('profile.edit');
    })->name('profile.edit');
});

// Admin analytics dashboard — gated by the 'admin' middleware alias
// (bootstrap/app.php), which checks users.is_admin server-side on every
// request. Nested under 'auth' implicitly since 'admin' itself requires
// an authenticated user (EnsureUserIsAdmin checks $request->user()).
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Stripe posts here directly — excluded from CSRF verification (see
// bootstrap/app.php) and verified instead by its own signature check
// inside the controller. Never add the 'auth' or 'web' session middleware
// here; Stripe's server has no session/CSRF token to present.
Route::post('/webhooks/stripe', StripeWebhookController::class)->name('webhooks.stripe');

require __DIR__.'/auth.php';
