<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Offer;
use App\Models\OfferSubscription;

class OfferSeeder extends Seeder
{
    public function run(): void
    {
        $offers = [
            [
                'slug' => 'solo',
                'name' => 'Offre Solo',
                'persons' => 1,
                'icon' => '/images/abonnements/Bronze.png',
                'description' => 'Idéal pour une personne. Des repas frais livrés à votre porte.',
                'sort_order' => 1,
                'subscriptions' => [
                    [
                        'name' => 'Abonnement 1',
                        'slug' => 'abonnement-1',
                        'meals_per_week' => 2,
                        'price' => 11500,
                        'description' => '2 repas par semaine pour 1 personne',
                        'features' => ['2 kits repas / semaine', 'Ingrédients frais et locaux', 'Fiches recettes illustrées', 'Livraison incluse'],
                        'popular' => false,
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Abonnement 2',
                        'slug' => 'abonnement-2',
                        'meals_per_week' => 4,
                        'price' => 22500,
                        'description' => '4 repas par semaine pour 1 personne',
                        'features' => ['4 kits repas / semaine', 'Ingrédients frais et locaux', 'Support prioritaire', 'Livraison flexible'],
                        'popular' => true,
                        'sort_order' => 2,
                    ],
                    [
                        'name' => 'Abonnement 3',
                        'slug' => 'abonnement-3',
                        'meals_per_week' => 6,
                        'price' => 34000,
                        'description' => '6 repas par semaine pour 1 personne',
                        'features' => ['6 kits repas / semaine', 'Économie maximale', 'Aide IA personnalisée', 'Pause facile'],
                        'popular' => false,
                        'sort_order' => 3,
                    ],
                ],
            ],
            [
                'slug' => 'duo',
                'name' => 'Offre Duo',
                'persons' => 2,
                'icon' => '/images/abonnements/Silver.png',
                'description' => 'Parfait pour un couple. Partagez des repas savoureux à deux.',
                'sort_order' => 2,
                'subscriptions' => [
                    [
                        'name' => 'Abonnement 1',
                        'slug' => 'abonnement-1',
                        'meals_per_week' => 2,
                        'price' => 22000,
                        'description' => '2 repas par semaine pour 2 personnes',
                        'features' => ['2 kits repas / semaine', 'Pour 2 personnes', 'Ingrédients frais et locaux', 'Livraison incluse'],
                        'popular' => false,
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Abonnement 2',
                        'slug' => 'abonnement-2',
                        'meals_per_week' => 4,
                        'price' => 45000,
                        'description' => '4 repas par semaine pour 2 personnes',
                        'features' => ['4 kits repas / semaine', 'Pour 2 personnes', 'Support prioritaire', 'Livraison flexible'],
                        'popular' => true,
                        'sort_order' => 2,
                    ],
                    [
                        'name' => 'Abonnement 3',
                        'slug' => 'abonnement-3',
                        'meals_per_week' => 6,
                        'price' => 65500,
                        'description' => '6 repas par semaine pour 2 personnes',
                        'features' => ['6 kits repas / semaine', 'Pour 2 personnes', 'Économie maximale', 'Pause facile'],
                        'popular' => false,
                        'sort_order' => 3,
                    ],
                ],
            ],
            [
                'slug' => 'famille',
                'name' => 'Offre Famille',
                'persons' => 4,
                'icon' => '/images/abonnements/gold.png',
                'description' => 'Pour toute la famille. 4 personnes, des recettes variées chaque semaine.',
                'sort_order' => 3,
                'subscriptions' => [
                    [
                        'name' => 'Abonnement 1',
                        'slug' => 'abonnement-1',
                        'meals_per_week' => 2,
                        'price' => 42000,
                        'description' => '2 repas par semaine pour 4 personnes',
                        'features' => ['2 kits repas / semaine', 'Pour 4 personnes', 'Ingrédients frais et locaux', 'Livraison incluse'],
                        'popular' => false,
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Abonnement 2',
                        'slug' => 'abonnement-2',
                        'meals_per_week' => 4,
                        'price' => 84000,
                        'description' => '4 repas par semaine pour 4 personnes',
                        'features' => ['4 kits repas / semaine', 'Pour 4 personnes', 'Aide IA personnalisée', 'Livraison flexible'],
                        'popular' => true,
                        'sort_order' => 2,
                    ],
                    [
                        'name' => 'Abonnement 3',
                        'slug' => 'abonnement-3',
                        'meals_per_week' => 6,
                        'price' => 126000,
                        'description' => '6 repas par semaine pour 4 personnes',
                        'features' => ['6 kits repas / semaine', 'Pour 4 personnes', 'Économie maximale', 'Conciergerie WhatsApp'],
                        'popular' => false,
                        'sort_order' => 3,
                    ],
                ],
            ],
            [
                'slug' => 'grande-famille',
                'name' => 'Offre Grande Famille',
                'persons' => 6,
                'icon' => '/images/abonnements/diamond.png',
                'description' => 'Pour les grandes familles. 6 personnes et plus, la liberté totale.',
                'sort_order' => 4,
                'subscriptions' => [
                    [
                        'name' => 'Abonnement 1',
                        'slug' => 'abonnement-1',
                        'meals_per_week' => 2,
                        'price' => 59000,
                        'description' => '2 repas par semaine pour 6+ personnes',
                        'features' => ['2 kits repas / semaine', 'Pour 6+ personnes', 'Ingrédients frais et locaux', 'Livraison incluse'],
                        'popular' => false,
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Abonnement 2',
                        'slug' => 'abonnement-2',
                        'meals_per_week' => 4,
                        'price' => 118000,
                        'description' => '4 repas par semaine pour 6+ personnes',
                        'features' => ['4 kits repas / semaine', 'Pour 6+ personnes', 'Aide IA personnalisée', 'Livraison flexible'],
                        'popular' => true,
                        'sort_order' => 2,
                    ],
                    [
                        'name' => 'Abonnement 3',
                        'slug' => 'abonnement-3',
                        'meals_per_week' => 6,
                        'price' => 177000,
                        'description' => '6 repas par semaine pour 6+ personnes',
                        'features' => ['6 kits repas / semaine', 'Pour 6+ personnes', 'Conciergerie WhatsApp', 'La liberté totale'],
                        'popular' => false,
                        'sort_order' => 3,
                    ],
                ],
            ],
        ];

        foreach ($offers as $offerData) {
            $subs = $offerData['subscriptions'];
            unset($offerData['subscriptions']);

            $offer = Offer::updateOrCreate(
                ['slug' => $offerData['slug']],
                $offerData
            );

            foreach ($subs as $sub) {
                OfferSubscription::updateOrCreate(
                    ['offer_id' => $offer->id, 'slug' => $sub['slug']],
                    $sub
                );
            }
        }
    }
}
