<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Demo Shopper',
            'email' => 'demo@example.com',
        ]);

        Product::factory()
            ->count(12)
            ->has(\Database\Factories\ProductVariantFactory::new()->count(3), 'variants')
            ->create();
    }
}
