<?php
// app/Services/AiService.php

namespace App\Services;

use App\Models\MealKit;
use App\Models\Offer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    private string $groqKey;
    private string $groqModel;
    private string $groqFallbackModel;
    private string $groqBaseUrl;

    private string $geminiKey;
    private string $geminiModel;
    private string $geminiBaseUrl;

    private int   $maxTokens;
    private float $temperature;

    public function __construct()
    {
        $this->groqKey           = config('services.groq.api_key');
        $this->groqModel         = config('services.groq.model');
        $this->groqFallbackModel = config('services.groq.fallback_model');
        $this->groqBaseUrl       = config('services.groq.base_url');

        $this->geminiKey     = config('services.gemini.api_key');
        $this->geminiModel   = config('services.gemini.model');
        $this->geminiBaseUrl = config('services.gemini.base_url');

        $this->maxTokens   = config('services.groq.max_tokens');
        $this->temperature = config('services.groq.temperature');
    }

    /**
     * Point d'entrée principal.
     * Ordre : Groq principal → Groq léger → Gemini → erreur.
     */
    public function chat(string $userMessage, array $history = []): array
    {
        $catalog  = $this->getKitsCatalog();
        $offersCatalog = $this->getOffersCatalog();
        $prompt   = $this->buildSystemPrompt($catalog, $offersCatalog);
        $messages = [
            ['role' => 'system', 'content' => $prompt],
            ...$history,
            ['role' => 'user', 'content' => $userMessage],
        ];

        // Tentative 1 — Groq modèle principal
        $response = $this->callGroq($messages, $this->groqModel);
        if ($response['success']) {
            Log::info('AiService: réponse via Groq principal.');
            return $this->parseResponse($response['content']);
        }

        // Tentative 2 — Groq modèle léger (quota tokens dépassé)
        if ($response['error_code'] === 429) {
            Log::warning('AiService: Groq principal quota 429, essai fallback Groq.');
            $response = $this->callGroq($messages, $this->groqFallbackModel);
            if ($response['success']) {
                Log::info('AiService: réponse via Groq fallback.');
                return $this->parseResponse($response['content']);
            }
        }

        // Tentative 3 — Gemini backup final
        Log::warning('AiService: Groq indisponible, bascule sur Gemini.');
        $response = $this->callGemini($prompt, $history, $userMessage);
        if ($response['success']) {
            Log::info('AiService: réponse via Gemini backup.');
            return $this->parseResponse($response['content']);
        }

        Log::error('AiService: Groq ET Gemini indisponibles.');
        return $this->errorResponse();
    }

    // ─────────────────────────────────────────────────────────
    //  APPEL GROQ
    //  Interface standard OpenAI /chat/completions
    // ─────────────────────────────────────────────────────────

    private function callGroq(array $messages, string $model): array
    {
        try {
            $response = Http::withoutVerifying()
                ->withToken($this->groqKey)
                ->timeout(25)
                ->post("{$this->groqBaseUrl}/chat/completions", [
                    'model'           => $model,
                    'messages'        => $messages,
                    'max_tokens'      => $this->maxTokens,
                    'temperature'     => $this->temperature,
                    'response_format' => ['type' => 'json_object'],
                ]);

            if ($response->failed()) {
                return ['success' => false, 'error_code' => $response->status(), 'content' => null];
            }

            return [
                'success' => true,
                'content' => $response->json('choices.0.message.content'),
            ];

        } catch (\Exception $e) {
            Log::error('Groq exception: ' . $e->getMessage());
            return ['success' => false, 'error_code' => 500, 'content' => null];
        }
    }

    // ─────────────────────────────────────────────────────────
    //  APPEL GEMINI
    //  Gemini a sa propre structure d'API, différente d'OpenAI.
    //  C'est pourquoi il a sa propre méthode séparée.
    // ─────────────────────────────────────────────────────────

    private function callGemini(string $systemPrompt, array $history, string $userMessage): array
    {
        try {
            // Convertir l'historique au format Gemini
            $contents = [];
            foreach ($history as $msg) {
                $contents[] = [
                    'role'  => $msg['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => $msg['content']]],
                ];
            }
            $contents[] = [
                'role'  => 'user',
                'parts' => [['text' => $userMessage]],
            ];

            $url = "{$this->geminiBaseUrl}/models/{$this->geminiModel}:generateContent?key={$this->geminiKey}";

            $response = Http::withoutVerifying()->timeout(30)->post($url, [
                'system_instruction' => [
                    'parts' => [['text' => $systemPrompt]],
                ],
                'contents'         => $contents,
                'generationConfig' => [
                    'maxOutputTokens'  => $this->maxTokens,
                    'temperature'      => $this->temperature,
                    'responseMimeType' => 'application/json',
                ],
            ]);

            if ($response->failed()) {
                return ['success' => false, 'error_code' => $response->status(), 'content' => null];
            }

            $content = $response->json('candidates.0.content.parts.0.text');
            return ['success' => true, 'content' => $content];

        } catch (\Exception $e) {
            Log::error('Gemini exception: ' . $e->getMessage());
            return ['success' => false, 'error_code' => 500, 'content' => null];
        }
    }

    // ─────────────────────────────────────────────────────────
    //  PROMPT SYSTÈME
    // ─────────────────────────────────────────────────────────

    private function buildSystemPrompt(string $kitsCatalog, string $offersCatalog): string
    {
        return <<<PROMPT
Tu es **KirefraisBot**, l'expert de Kirefrais, le service n°1 d'abonnements de kits repas à Lomé, au Togo.
Ton but principal est d'orienter les clients vers nos offres d'abonnement (Solo, Duo, Famille, Grande Famille). 
Tu peux utiliser les plats de notre catalogue pour donner envie, mais explique toujours que nos plats sont disponibles via nos formules d'abonnement.

## 📦 CATALOGUE RÉEL DES PLATS (POUR ILLUSTRATION)
Voici les plats actuellement disponibles.
$kitsCatalog

## 💳 NOS ABONNEMENTS (OFFRES)
Voici nos abonnements que tu dois recommander :
$offersCatalog

## 🎯 RÈGLES DE RÉPONSE
1. **Priorité aux Abonnements** : Propose TOUJOURS nos abonnements (Solo, Duo, Famille, Grande Famille) plutôt que d'acheter des plats à l'unité.
2. **Exemples de plats** : Utilise les plats du catalogue pour illustrer ce qu'ils pourront manger avec leur abonnement.
3. **Vérité Absolue** : Ne mentionne que les plats du catalogue ci-dessus.
4. **Corrélation** : Si l'utilisateur demande "Qu'est-ce qu'il y a ?", liste quelques exemples de plats et propose un abonnement.
4. **Ton** : Chaleureux, accueillant ("Bienvenue chez Kirefrais !"), utilise des expressions locales comme "Woezor" (bienvenue) ou "Akpé" (merci) si approprié.
5. **Focus** : Si la question n'est pas liée à la nourriture, la cuisine ou la santé, réponds poliment que ton expertise se limite à l'univers Kirefrais.
6. **Anonymat des Identifiants** : Ne mentionne JAMAIS, AU GRAND JAMAIS, les ID (ex: "ID: 4") dans ta réponse texte `reply`. Utilise uniquement le nom des plats et abonnement.

## ⚠️ CONTRAINTES TECHNIQUES
- Réponds UNIQUEMENT en JSON.
- `offer_ids` : Doit contenir entre 1 et 4 IDs issus des abonnements (offres). Ne jamais laisser vide. Recommande toujours l'abonnement le plus adapté (Solo, Duo, etc.).
- `steps` : Liste de chaînes de caractères. Format : "Étape X : [Action]".  

## STRUCTURE JSON ATTENDUE
{
  "type": "conseil | preparation | nutrition | autre",
  "reply": "Ta réponse complète ici...",
  "steps": ["Étape 1 : ...", "Étape 2 : ..."],
  "tips": ["Conseil bonus 1", "Conseil bonus 2"],
  "offer_ids": [1, 2]
}

- "type"      : le type de demande détecté
- "reply"     : réponse complète en français (markdown accepté)
- "steps"     : étapes de préparation si pertinent, sinon []
- "tips"      : 1 à 3 conseils bonus courts, sinon []
- "offer_ids" : 1 à 4 IDs d'abonnements recommandés, jamais vide
PROMPT;
    }

    // ─────────────────────────────────────────────────────────
    //  CATALOGUE DES KITS
    // ─────────────────────────────────────────────────────────

    private function getKitsCatalog(): string
    {
        $kits = MealKit::active()
            ->with('category')
            ->get(['id', 'name', 'description', 'ingredients', 'recipe_steps', 'category_id',
                   'price_1p', 'prep_time', 'difficulty',
                   'calories', 'is_vegetarian']);

        if ($kits->isEmpty()) return 'Catalogue non disponible.';

        return $kits->map(function($k) {
            $steps = collect($k->recipe_steps)->map(fn($s, $i) => ($i+1).'. '.$s['title'])->join(', ');
            return sprintf(
                'ID:%d | Nom:%s | Catégorie:%s | Calories:%d kcal | Temps:%d min | Difficulté:%s | Prix:%s FCFA | Végétarien:%s | Ingrédients:%s | Étapes:%s',
                $k->id,
                $k->name,
                $k->category?->name ?? 'Général',
                $k->calories,
                $k->prep_time,
                $k->difficulty,
                number_format($k->price_1p, 0, '.', ' '),
                $k->is_vegetarian ? 'Oui' : 'Non',
                is_array($k->ingredients) ? implode(', ', $k->ingredients) : $k->ingredients,
                $steps ?: 'Non détaillées'
            );
        })->join("\n");
    }

    private function getOffersCatalog(): string
    {
        $offers = Offer::where('is_active', true)->get();
        if ($offers->isEmpty()) return 'Aucun abonnement disponible.';

        return $offers->map(function($o) {
            return sprintf('ID:%d | Nom:%s | Personnes:%d | Description:%s', 
                $o->id, $o->name, $o->persons, $o->description
            );
        })->join("\n");
    }

    // ─────────────────────────────────────────────────────────
    //  PARSER LA RÉPONSE JSON
    // ─────────────────────────────────────────────────────────

    private function parseResponse(?string $content): array
    {
        if (!$content) return $this->errorResponse();

        try {
            $parsed = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            $offerIds = $parsed['offer_ids'] ?? [];
            $offers   = [];

            if (!empty($offerIds)) {
                $offers = Offer::whereIn('id', $offerIds)
                    ->where('is_active', true)
                    ->get()
                    ->map(function($o) {
                        return [
                            'id'          => $o->id,
                            'name'        => $o->name,
                            'slug'        => $o->slug,
                            'persons'     => $o->persons,
                            'icon'        => $o->icon,
                            'description' => $o->description,
                        ];
                    })
                    ->toArray();
            }

            return [
                'type'   => $parsed['type']  ?? 'conseil',
                'reply'  => $parsed['reply'] ?? '',
                'steps'  => $parsed['steps'] ?? [],
                'tips'   => $parsed['tips']  ?? [],
                'offers' => $offers,
            ];

        } catch (\JsonException $e) {
            Log::error('AiService JSON parse error: ' . $e->getMessage());
            return [
                'type'   => 'conseil',
                'reply'  => $content,
                'steps'  => [],
                'tips'   => [],
                'offers' => [],
            ];
        }
    }

    private function errorResponse(): array
    {
        return [
            'type'   => 'error',
            'reply'  => 'Je suis temporairement indisponible. Réessayez dans quelques instants.',
            'steps'  => [],
            'tips'   => [],
            'offers' => [],
        ];
    }
}
