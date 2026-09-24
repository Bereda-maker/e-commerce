<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            // pending -> paid | payment_failed. Inventory is decremented
            // when the order is created (pending), inside the same
            // transaction as the lockForUpdate() stock check — not
            // deferred until the webhook confirms payment. See
            // CheckoutService and README "Error handling" for why a
            // failed payment must release the reserved stock back.
            $table->string('status')->default('pending');
            $table->unsignedInteger('total_cents');
            // Stripe's PaymentIntent id — the join key the webhook uses
            // to find this order without trusting anything else in the
            // webhook payload. Unique + nullable: set once checkout
            // creates the PaymentIntent.
            $table->string('stripe_payment_intent_id')->nullable()->unique();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained();
            // Snapshot the name/price at purchase time — never join back
            // to products/product_variants to display order history,
            // since the product's current name or price shouldn't be
            // able to silently rewrite what a customer sees they paid.
            $table->string('product_name');
            $table->string('variant_label')->nullable(); // e.g. "Medium / Blue"
            $table->unsignedInteger('unit_price_cents');
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
