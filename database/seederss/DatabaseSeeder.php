<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            MealKitSeeder::class,
            DeliveryZoneSeeder::class,
            AddressSeeder::class,
        ]);
    }
}
