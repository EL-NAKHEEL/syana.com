<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Real (migrated) content, unpublished until the owner's review. Safe in every environment.
        $this->call(LegacyContentSeeder::class);

        // Admin accounts are created with `php artisan make:filament-user` (MFA is enrolled on first login).
    }
}
