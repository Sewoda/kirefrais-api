<?php
// database/seeders/DeliveryZoneSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            // ── Lomé centre et quartiers proches ──────────────────
            [
                'name'              => 'Lomé Centre',
                'city'              => 'Lomé',
                'delivery_fee'      => 1000,
                'estimated_minutes' => 25,
                'is_active'         => true,
            ],
            [
                'name'              => 'Adidogomé',
                'city'              => 'Lomé',
                'delivery_fee'      => 1200,
                'estimated_minutes' => 35,
                'is_active'         => true,
            ],
            [
                'name'              => 'Agoè',
                'city'              => 'Lomé',
                'delivery_fee'      => 1500,
                'estimated_minutes' => 45,
                'is_active'         => true,
            ],
            [
                'name'              => 'Bè',
                'city'              => 'Lomé',
                'delivery_fee'      => 1000,
                'estimated_minutes' => 20,
                'is_active'         => true,
            ],
            [
                'name'              => 'Tokoin',
                'city'              => 'Lomé',
                'delivery_fee'      => 1000,
                'estimated_minutes' => 20,
                'is_active'         => true,
            ],
            [
                'name'              => 'Nyékonakpoè',
                'city'              => 'Lomé',
                'delivery_fee'      => 1000,
                'estimated_minutes' => 25,
                'is_active'         => true,
            ],
            [
                'name'              => 'Hédzranawoé',
                'city'              => 'Lomé',
                'delivery_fee'      => 1200,
                'estimated_minutes' => 30,
                'is_active'         => true,
            ],
            [
                'name'              => 'Djidjolé',
                'city'              => 'Lomé',
                'delivery_fee'      => 1500,
                'estimated_minutes' => 40,
                'is_active'         => true,
            ],
            [
                'name'              => 'Légbassito',
                'city'              => 'Lomé',
                'delivery_fee'      => 1500,
                'estimated_minutes' => 40,
                'is_active'         => true,
            ],
            [
                'name'              => 'Cacaveli',
                'city'              => 'Lomé',
                'delivery_fee'      => 1000,
                'estimated_minutes' => 20,
                'is_active'         => true,
            ],
            [
                'name'              => 'Kodjoviakopé',
                'city'              => 'Lomé',
                'delivery_fee'      => 1000,
                'estimated_minutes' => 25,
                'is_active'         => true,
            ],
            [
                'name'              => 'Akodésséwa',
                'city'              => 'Lomé',
                'delivery_fee'      => 1200,
                'estimated_minutes' => 30,
                'is_active'         => true,
            ],

            // ── Périphérie de Lomé ────────────────────────────────
            [
                'name'              => 'Anfoin',
                'city'              => 'Lomé',
                'delivery_fee'      => 2000,
                'estimated_minutes' => 55,
                'is_active'         => true,
            ],
            [
                'name'              => 'Tsévié',
                'city'              => 'Tsévié',
                'delivery_fee'      => 2500,
                'estimated_minutes' => 70,
                'is_active'         => false,
            ],
        ];

        foreach ($zones as $zone) {
            DB::table('delivery_zones')->insert(array_merge($zone, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}