<?php
// database/seeders/MealKitSeeder.php

namespace Database\Seeders;

use App\Models\MealKit;
use App\Models\Category;
use Illuminate\Database\Seeder;

class MealKitSeeder extends Seeder
{
    public function run(): void
    {
        $catViande = Category::where('slug', 'viandes')->first();
        $catRiz = Category::where('slug', 'riz')->first();
        $catVeg = Category::where('slug', 'vegetarien')->first();

        $kits = [
            [
                'category_id' => $catViande->id,
                'name'        => 'Poulet Yassa',
                'description' => 'Le célèbre plat sénégalo-togolais. Poulet mariné aux oignons et citron, mijoté lentement.',
                'ingredients' => 'Poulet fermier, oignons, citron vert, moutarde, huile, sel, poivre, piment',
                'images'      => ['https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?auto=format&fit=crop&q=80&w=800'],
                'prep_time'   => 35,
                'difficulty'  => 'easy',
                'calories'    => 520,
                'proteins'    => 42,
                'carbs'       => 18,
                'fats'        => 28,
                'fiber'       => 3,
                'price_1p'    => 3500,
                'price_2p'    => 6000,
                'price_4p'    => 11000,
                'is_vegetarian' => false,
                'recipe_steps'  => [
                    ['title' => 'Mariner le poulet', 'content' => 'Mélangez le jus de citron, la moutarde, le sel et le poivre. Enrobez les morceaux de poulet et laissez mariner 30 minutes au frais.'],
                    ['title' => 'Saisir le poulet', 'content' => 'Faites chauffer l\'huile dans une poêle et saisissez les morceaux de poulet de chaque côté jusqu\'à obtenir une belle coloration dorée.'],
                    ['title' => 'Préparer la sauce', 'content' => 'Émincez finement les oignons. Faites-les suer dans l\'huile avec le piment jusqu\'à ce qu\'ils soient translucides.'],
                    ['title' => 'Mijoter ensemble', 'content' => 'Ajoutez le poulet aux oignons, arrosez avec la marinade et couvrez. Laissez mijoter 25 minutes à feu doux.'],
                ],
            ],
            [
                'category_id' => $catRiz->id,
                'name'        => 'Riz au Gras',
                'description' => 'Riz jollof togolais, cuisiné avec tomates fraîches, épices locales et légumes de saison.',
                'ingredients' => 'Riz long, tomates, oignons, poivrons, huile de palme, bouillon, épices',
                'images'      => ['https://images.unsplash.com/photo-1512058560560-cf0145aca19b?auto=format&fit=crop&q=80&w=800'],
                'prep_time'   => 45,
                'difficulty'  => 'medium',
                'calories'    => 480,
                'proteins'    => 12,
                'carbs'       => 72,
                'fats'       => 15,
                'fiber'       => 4,
                'price_1p'    => 2800,
                'price_2p'    => 5000,
                'price_4p'    => 9000,
                'is_vegetarian' => false,
                'recipe_steps'  => [
                    ['title' => 'Préparer la base tomate', 'content' => 'Mixez les tomates avec les oignons et les poivrons pour obtenir une purée lisse.'],
                    ['title' => 'Faire revenir', 'content' => 'Faites chauffer l\'huile de palme et versez la purée de tomates. Laissez réduire 15 minutes en remuant régulièrement.'],
                    ['title' => 'Cuire le riz', 'content' => 'Ajoutez le riz lavé et le bouillon. Mélangez bien, couvrez et laissez cuire à feu doux pendant 25 minutes sans soulever le couvercle.'],
                ],
            ],
            [
                'category_id' => $catVeg->id,
                'name'        => 'Sauce Graine Végétarienne',
                'description' => 'Sauce noix de palme traditionnelle togolaise, version végétarienne avec légumes frais.',
                'ingredients' => 'Noix de palme, gombo, aubergines, tomates, oignons, piment, sel',
                'images'      => ['https://images.unsplash.com/photo-1547592166-23ac45744acd?auto=format&fit=crop&q=80&w=800'],
                'prep_time'   => 50,
                'difficulty'  => 'hard',
                'calories'    => 380,
                'proteins'    => 8,
                'carbs'       => 22,
                'fats'        => 30,
                'fiber'       => 6,
                'price_1p'    => 2500,
                'price_2p'    => 4500,
                'price_4p'    => 8500,
                'is_vegetarian' => true,
                'recipe_steps'  => [
                    ['title' => 'Extraire le jus de palme', 'content' => 'Pressez les noix de palme pour obtenir le jus concentré. Filtrez pour enlever les fibres.'],
                    ['title' => 'Préparer les légumes', 'content' => 'Coupez les aubergines en dés et le gombo en rondelles. Émincez les oignons et les tomates.'],
                    ['title' => 'Cuire la sauce', 'content' => 'Portez le jus de palme à ébullition, puis ajoutez les légumes progressivement. Laissez mijoter 40 minutes à couvert.'],
                    ['title' => 'Assaisonner et servir', 'content' => 'Rectifiez l\'assaisonnement avec le sel et le piment. Servez chaud avec du fufu ou de la pâte de maïs.'],
                ],
            ],
        ];

        foreach ($kits as $kit) {
            MealKit::create($kit);
        }
    }
}
