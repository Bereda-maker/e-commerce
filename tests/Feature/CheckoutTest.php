<?php

use App\Contracts\PaymentGateway;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CheckoutService;

test('checkout decrements stock and creates a pending order', function () {
    $variant = ProductVariant::factory()->create(['stock_count' => 5, 'price_cents' => 2000]);
    $buyer = User::factory()->create();
    $cart = Cart::factory()->for($buyer)->create();
    CartItem::factory()->for($cart)->create(['product_variant_id' => $variant->id, 'quantity' => 2]);

    $this->mock(PaymentGateway::class, function ($mock) {
        $mock->shouldReceive('createPaymentIntent')
            ->once()
            ->andReturn((object) ['id' => 'pi_test_123', 'client_secret' => 'secret']);
    });

    $order = app(CheckoutService::class)->checkout($buyer, $cart->fresh('items'));

    expect($order->status)->toBe(Order::STATUS_PENDING);
    expect($order->total_cents)->toBe(4000);
    expect($variant->fresh()->stock_count)->toBe(3);
    expect($cart->fresh()->items)->toBeEmpty();
});

test('checkout throws when requested quantity exceeds stock', function () {
    $variant = ProductVariant::factory()->create(['stock_count' => 1]);
    $buyer = User::factory()->create();
    $cart = Cart::factory()->for($buyer)->create();
    CartItem::factory()->for($cart)->create(['product_variant_id' => $variant->id, 'quantity' => 2]);

    $this->mock(PaymentGateway::class); // should never be called

    expect(fn () => app(CheckoutService::class)->checkout($buyer, $cart->fresh('items')))
        ->toThrow(\App\Exceptions\OutOfStockException::class);

    expect($variant->fresh()->stock_count)->toBe(1); // untouched — the transaction rolled back
});

test('cancelling a pending order releases stock and cancels the PaymentIntent', function () {
    $variant = ProductVariant::factory()->create(['stock_count' => 5]);
    $order = Order::factory()->create([
        'status' => Order::STATUS_PENDING,
        'stripe_payment_intent_id' => 'pi_test_cancel_me',
    ]);
    $order->items()->create([
        'product_variant_id' => $variant->id,
        'product_name' => 'Test product',
        'unit_price_cents' => 1000,
        'quantity' => 2,
    ]);
    // Stock already reflects the earlier decrement from checkout — cancel should add it back.
    $variant->decrement('stock_count', 2);

    $this->mock(PaymentGateway::class, function ($mock) {
        $mock->shouldReceive('cancelPaymentIntent')->once()->with('pi_test_cancel_me');
    });

    app(CheckoutService::class)->cancelOrder($order);

    expect($order->fresh()->status)->toBe(Order::STATUS_CANCELLED);
    expect($variant->fresh()->stock_count)->toBe(5); // released back
});

test('cancelling a non-pending order is a no-op', function () {
    $order = Order::factory()->create(['status' => Order::STATUS_PAID]);

    $this->mock(PaymentGateway::class, function ($mock) {
        $mock->shouldReceive('cancelPaymentIntent')->once(); // still attempted on Stripe's side...
    });

    app(CheckoutService::class)->cancelOrder($order);

    expect($order->fresh()->status)->toBe(Order::STATUS_PAID); // ...but local status is untouched
});
