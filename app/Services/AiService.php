<?php
// app/Services/AiService.php

namespace App\Services;

use App\Models\MealKit;
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
        $prompt   = $this->buildSystemPrompt($catalog);
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
            $response = Http::withToken($this->groqKey)
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

            $response = Http::timeout(30)->post($url, [
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

    private function buildSystemPrompt(string $kitsCatalog): string
    {
        return <<<PROMPT
Tu es **KirefraisBot**, l'expert culinaire de Kirefrais, le service n°1 de kits repas à Lomé, au Togo.
Ton but est d'aider les clients à manger sainement, à découvrir des recettes locales et à choisir les meilleurs kits Kirefrais.

## 📦 CATALOGUE RÉEL (SOURCE UNIQUE)
Voici les kits actuellement disponibles en stock. Tu ne dois JAMAIS en inventer d'autres.
$kitsCatalog

## 🎯 RÈGLES DE RÉPONSE
1. **Vérité Absolue** : Si l'utilisateur demande "Qu'est-ce qu'il y a ?", liste les noms du catalogue.
2. **Préparation** : Si on te demande comment cuisiner un plat du catalogue, utilise les ingrédients exacts listés ci-dessus. Pour les étapes, développe les points mentionnés dans "Étapes" pour donner une recette claire.
3. **Corrélation STRICTE** : Tes recommandations de kits (`kit_ids`) doivent être DIRECTEMENT liées à la demande. 
   - Si l'utilisateur parle d'un plat spécifique du catalogue, tu DOIS le recommander.
   - Si la demande est générale (ex: "santé"), choisis les kits les plus adaptés.
   - Ne propose JAMAIS de kits sans rapport (ex: proposer du riz si on parle de dessert).
4. **Ton** : Chaleureux, accueillant ("Bienvenue chez Kirefrais !"), utilise des expressions locales comme "Woezor" (bienvenue) ou "Akpé" (merci) si approprié.
5. **Focus** : Si la question n'est pas liée à la nourriture, la cuisine ou la santé, réponds poliment que ton expertise se limite à l'univers Kirefrais.

## ⚠️ CONTRAINTES TECHNIQUES
- Réponds UNIQUEMENT en JSON.
- `kit_ids` : Doit contenir entre 1 et 3 IDs issus du catalogue. Ne jamais laisser vide.
- `steps` : Liste de chaînes de caractères. Format : "Étape X : [Action]".

## STRUCTURE JSON ATTENDUE
{
  "type": "conseil | preparation | nutrition | autre",
  "reply": "Ta réponse complète ici...",
  "steps": ["Étape 1 : ...", "Étape 2 : ..."],
  "tips": ["Conseil bonus 1", "Conseil bonus 2"],
  "kit_ids": [1, 5, 12]
}

- "type"    : le type de demande détecté
- "reply"   : réponse complète en français (markdown accepté)
- "steps"   : étapes de préparation si pertinent, sinon []
- "tips"    : 1 à 3 conseils bonus courts, sinon []
- "kit_ids" : 1 à 3 IDs de kits recommandés, jamais vide
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

    // ─────────────────────────────────────────────────────────
    //  PARSER LA RÉPONSE JSON
    // ─────────────────────────────────────────────────────────

    private function parseResponse(?string $content): array
    {
        if (!$content) return $this->errorResponse();

        try {
            $parsed = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            $kitIds = $parsed['kit_ids'] ?? [];
            $kits   = [];

            if (!empty($kitIds)) {
                $kits = MealKit::whereIn('id', $kitIds)
                    ->active()
                    ->with('category')
                    ->get()
                    ->map(fn($k) => [
                        'id'         => $k->id,
                        'name'       => $k->name,
                        'slug'       => $k->slug,
                        'images'     => $k->images,
                        'image'      => $k->images[0] ?? null, // Compatibilité
                        'prices'     => [
                            '1p' => $k->price_1p,
                            '2p' => $k->price_2p,
                            '4p' => $k->price_4p,
                        ],
                        'price_1p'   => $k->price_1p,
                        'prep_time'  => $k->prep_time,
                        'difficulty' => $k->difficulty,
                        'calories'   => $k->calories,
                        'rating_avg' => $k->rating_avg,
                        'category'   => $k->category?->name,
                    ])
                    ->toArray();
            }

            return [
                'type'  => $parsed['type']  ?? 'conseil',
                'reply' => $parsed['reply'] ?? '',
                'steps' => $parsed['steps'] ?? [],
                'tips'  => $parsed['tips']  ?? [],
                'kits'  => $kits,
            ];

        } catch (\JsonException $e) {
            Log::error('AiService JSON parse error: ' . $e->getMessage());
            return [
                'type'  => 'conseil',
                'reply' => $content,
                'steps' => [],
                'tips'  => [],
                'kits'  => [],
            ];
        }
    }

    private function errorResponse(): array
    {
        return [
            'type'  => 'error',
            'reply' => 'Je suis temporairement indisponible. Réessayez dans quelques instants.',
            'steps' => [],
            'tips'  => [],
            'kits'  => [],
        ];
    }
}
