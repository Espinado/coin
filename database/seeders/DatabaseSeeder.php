<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            PlatformSettingsSeeder::class,
            PlanSeeder::class,
            LegalPageSeeder::class,
            CoinDemoSeeder::class,
        ]);
    }
}
