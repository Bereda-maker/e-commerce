<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            // unsignedInteger, never allowed negative — the app-level
            // invariant lockForUpdate()+decrement() in CheckoutService
            // enforces is that this can never go below zero, even under
            // two simultaneous checkouts for the last unit. See
            // app/Services/CheckoutService.php.
            $table->unsignedInteger('stock_count')->default(0);
            $table->unsignedInteger('price_cents');
            $table->timestamps();

            $table->unique(['product_id', 'size', 'color']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
