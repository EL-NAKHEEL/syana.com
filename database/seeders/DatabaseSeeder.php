<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Real (migrated) content, unpublished until the owner's review. Safe in every environment.
        $this->call(LegacyContentSeeder::class);

        // Fake catalog for local development only (unpublished; skipped in production).
        if (app()->environment('local')) {
            $this->call(DemoSeeder::class);
        }

        // Admin accounts are created with `php artisan make:filament-user` (MFA is enrolled on first login).
    }
}
