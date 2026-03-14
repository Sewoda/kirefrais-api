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
                'description' => 'Recettes emblématiques de la cuisine togolaise et ouest-africaine',
                'icon'        => 'bowl-food',
                'color'       => '#1B8A4A',
                'is_active'   => true,
            ],
            [
                'name'        => 'Soupes & Sauces',
                'slug'        => 'soupes-sauces',
                'description' => 'Sauces mijotées et soupes nourrissantes à base de légumes locaux',
                'icon'        => 'pot',
                'color'       => '#F4A400',
                'is_active'   => true,
            ],
            [
                'name'        => 'Grillades',
                'slug'        => 'grillades',
                'description' => 'Viandes et poissons marinés, grillés à la braise',
                'icon'        => 'fire',
                'color'       => '#EF4444',
                'is_active'   => true,
            ],
            [
                'name'        => 'Poissons & Fruits de mer',
                'slug'        => 'poissons-fruits-de-mer',
                'description' => 'Poissons frais du golfe de Guinée et fruits de mer',
                'icon'        => 'fish',
                'color'       => '#3B82F6',
                'is_active'   => true,
            ],
            [
                'name'        => 'Végétarien',
                'slug'        => 'vegetarien',
                'description' => 'Plats 100% végétaux riches en protéines et fibres',
                'icon'        => 'leaf',
                'color'       => '#22C55E',
                'is_active'   => true,
            ],
            [
                'name'        => 'Petit-déjeuner',
                'slug'        => 'petit-dejeuner',
                'description' => 'Kits pour bien démarrer la journée à la togolaise',
                'icon'        => 'sun',
                'color'       => '#F97316',
                'is_active'   => true,
            ],
            [
                'name'        => 'Healthy & Fitness',
                'slug'        => 'healthy-fitness',
                'description' => 'Repas équilibrés faibles en calories, riches en nutriments',
                'icon'        => 'heart',
                'color'       => '#EC4899',
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