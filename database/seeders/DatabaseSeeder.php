<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Safe to run more than once: the two named demo accounts are
     * upserted by email rather than blindly created, so re-running this
     * seeder (e.g. after adding a new feature that needs a fresh admin
     * account) doesn't crash on a duplicate-email constraint the way a
     * plain create() would on a second run.
     */
    public function run(): void
    {
        // Direct attribute assignment rather than updateOrCreate()'s
        // mass-assignment path deliberately — is_admin and
        // email_verified_at aren't in User::$fillable (is_admin
        // shouldn't be mass-assignable from anywhere a real request could
        // reach), and fill()-based mass assignment would silently drop
        // both rather than erroring, leaving "admin@example.com" as a
        // regular non-admin account with no visible failure.
        $shopper = User::firstOrNew(['email' => 'demo@example.com']);
        $shopper->name = 'Demo Shopper';
        $shopper->password = Hash::make('password');
        $shopper->email_verified_at = now();
        $shopper->save();

        $admin = User::firstOrNew(['email' => 'admin@example.com']);
        $admin->name = 'Demo Admin';
        $admin->password = Hash::make('password');
        $admin->email_verified_at = now();
        $admin->is_admin = true;
        $admin->save();

        // Only seed the demo catalog if it's actually empty — avoids
        // piling up duplicate products on a second seed run.
        if (Product::count() === 0) {
            Product::factory()
                ->count(12)
                ->has(\Database\Factories\ProductVariantFactory::new()->count(3), 'variants')
                ->create();
        }
    }
}
