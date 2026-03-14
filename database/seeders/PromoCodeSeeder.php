<?php
// database/seeders/PromoCodeSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        $promos = [
            [
                'code'       => 'BIENVENUE',
                'type'       => 'percent',
                'value'      => 15,
                'min_order'  => 5000,
                'max_uses'   => 500,
                'used_count' => 0,
                'expires_at' => now()->addMonths(3),
                'is_active'  => true,
            ],
            [
                'code'       => 'LOME2026',
                'type'       => 'fixed',
                'value'      => 2000,
                'min_order'  => 8000,
                'max_uses'   => 200,
                'used_count' => 0,
                'expires_at' => now()->addMonths(2),
                'is_active'  => true,
            ],
            [
                'code'       => 'FRESHKITS10',
                'type'       => 'percent',
                'value'      => 10,
                'min_order'  => 0,
                'max_uses'   => null,
                'used_count' => 0,
                'expires_at' => now()->addYear(),
                'is_active'  => true,
            ],
            [
                'code'       => 'FAMILLE',
                'type'       => 'fixed',
                'value'      => 3000,
                'min_order'  => 15000,
                'max_uses'   => 100,
                'used_count' => 0,
                'expires_at' => now()->addMonths(6),
                'is_active'  => true,
            ],
            [
                'code'       => 'TOGO225',
                'type'       => 'fixed',
                'value'      => 1500,
                'min_order'  => 6000,
                'max_uses'   => 300,
                'used_count' => 0,
                'expires_at' => now()->addMonth(),
                'is_active'  => true,
            ],
        ];

        foreach ($promos as $promo) {
            DB::table('promo_codes')->insert(array_merge($promo, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}