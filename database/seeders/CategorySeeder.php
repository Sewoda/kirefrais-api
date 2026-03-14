<?php
// database/seeders/CategorySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Plats traditionnels',
                'slug'        => 'plats-traditionnels',
                'icon'        => 'bowl-food',
                'is_active'   => true,
            ],
            [
                'name'        => 'Soupes & Sauces',
                'slug'        => 'soupes-sauces',
                'icon'        => 'pot',
                'is_active'   => true,
            ],
            [
                'name'        => 'Grillades',
                'slug'        => 'grillades',
                'icon'        => 'fire',
                'is_active'   => true,
            ],
            [
                'name'        => 'Poissons & Fruits de mer',
                'slug'        => 'poissons-fruits-de-mer',
                'icon'        => 'fish',
                'is_active'   => true,
            ],
            [
                'name'        => 'Végétarien',
                'slug'        => 'vegetarien',
                'icon'        => 'leaf',
                'is_active'   => true,
            ],
            [
                'name'        => 'Petit-déjeuner',
                'slug'        => 'petit-dejeuner',
                'icon'        => 'sun',
                'is_active'   => true,
            ],
            [
                'name'        => 'Healthy & Fitness',
                'slug'        => 'healthy-fitness',
                'icon'        => 'heart',
                'is_active'   => true,
            ],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->insert(array_merge($cat, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
