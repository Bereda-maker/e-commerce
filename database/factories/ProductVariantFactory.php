<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####-??')),
            'size' => fake()->randomElement(['S', 'M', 'L', 'XL']),
            'color' => fake()->safeColorName(),
            'stock_count' => fake()->numberBetween(0, 50),
            'price_cents' => fake()->numberBetween(1500, 12000),
        ];
    }

    /** Used by the concurrency test: exactly one unit left. */
    public function lastUnit(): static
    {
        return $this->state(fn () => ['stock_count' => 1]);
    }
}
