<?php

namespace Database\Seeders;

use App\Models\MealKit;
use Illuminate\Database\Seeder;

class KitSeeder extends Seeder
{
    public function run(): void
    {
        MealKit::create([
            'name' => 'Wakitchi (Riz et Haricots)',
            'description' => 'Un classique togolais savoureux accompagné de sauce pimentée et d\'œufs.',
            'image' => 'https://res.cloudinary.com/demo/image/upload/v1625211144/samples/food/fish-vegetables.jpg',
            'price_1p' => 1500,
            'price_2p' => 2800,
            'price_4p' => 5200,
            'prep_time' => 45,
            'difficulty' => 2,
            'is_vegetarian' => false,
            'is_active' => true,
            'rating' => 4.8,
        ]);

        MealKit::create([
            'name' => 'Fufu et Soupe Légère',
            'description' => 'Kit complet pour préparer votre fufu frais avec une soupe légère à la viande.',
            'image' => 'https://res.cloudinary.com/demo/image/upload/v1625211144/samples/food/pot-mussels.jpg',
            'price_1p' => 2000,
            'price_2p' => 3800,
            'price_4p' => 7000,
            'prep_time' => 60,
            'difficulty' => 3,
            'is_vegetarian' => false,
            'is_active' => true,
            'rating' => 4.9,
        ]);

        MealKit::create([
            'name' => 'Salade de Couscous aux Légumes',
            'description' => 'Option végétarienne fraîche et rapide pour vos déjeuners à Lomé.',
            'image' => 'https://res.cloudinary.com/demo/image/upload/v1625211143/samples/food/shrimp.jpg',
            'price_1p' => 1200,
            'price_2p' => 2200,
            'price_4p' => 4000,
            'prep_time' => 15,
            'difficulty' => 1,
            'is_vegetarian' => true,
            'is_active' => true,
            'rating' => 4.5,
        ]);
    }
}
