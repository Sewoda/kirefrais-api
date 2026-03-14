<?php

namespace Database\Seeders;

use App\Models\DeliveryZone;
use Illuminate\Database\Seeder;

class DeliveryZoneSeeder extends Seeder
{
    public function run(): void
    {
        DeliveryZone::create([
            'name'         => 'Lomé - Centre',
            'city'         => 'lome',
            'delivery_fee' => 500,
            'is_active'    => true,
        ]);

        DeliveryZone::create([
            'name'         => 'Baguidat / Avepozo',
            'city'         => 'lome',
            'delivery_fee' => 1000,
            'is_active'    => true,
        ]);

        DeliveryZone::create([
            'name'         => 'Adeticopé',
            'city'         => 'lome',
            'delivery_fee' => 1500,
            'is_active'    => true,
        ]);
    }
}
