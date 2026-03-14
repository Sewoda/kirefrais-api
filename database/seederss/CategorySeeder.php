<?php
// database/seeders/CategorySeeder.php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Viandes',     'slug' => 'viandes',    'icon' => '🍗'],
            ['name' => 'Poissons',    'slug' => 'poissons',   'icon' => '🐟'],
            ['name' => 'Végétarien',  'slug' => 'vegetarien', 'icon' => '🥗'],
            ['name' => 'Soupes',      'slug' => 'soupes',     'icon' => '🍲'],
            ['name' => 'Riz & Céréales', 'slug' => 'riz',     'icon' => '🍚'],
            ['name' => 'Spécialités', 'slug' => 'specialites','icon' => '⭐'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
