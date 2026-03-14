<?php
// app/Http/Controllers/KitController.php

namespace App\Http\Controllers;

use App\Models\MealKit;
use App\Http\Resources\KitResource;
use Illuminate\Http\Request;

class KitController extends Controller
{
    // ── Liste avec filtres ────────────────────────────────────
    public function index(Request $request)
    {
        $query = MealKit::active()->with('category');

        // Filtre par catégorie
        if ($request->category_id) {
            $query->byCategory($request->category_id);
        }

        // Filtre végétarien
        if ($request->vegetarian) {
            $query->vegetarian();
        }

        // Filtre par difficulté
        if ($request->difficulty) {
            $query->where('difficulty', $request->difficulty);
        }

        // Filtre par temps de préparation
        if ($request->max_time) {
            $query->where('prep_time', '<=', $request->max_time);
        }

        // Recherche par nom
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Tri
        $sort = $request->sort ?? 'order_count';
        $direction = 'desc';

        if ($sort === 'price_1p') {
            $direction = 'asc';
        }

        if (in_array($sort, ['order_count', 'rating_avg', 'price_1p', 'created_at'])) {
            $query->orderBy($sort, $direction);
        } else {
            $query->latest();
        }

        $kits = $query->paginate(12);

        return KitResource::collection($kits);
    }

    // ── Détail d'un kit ───────────────────────────────────────
    public function show(string $slug)
    {
        $kit = MealKit::active()
            ->with(['category', 'approvedReviews.user'])
            ->where('slug', $slug)
            ->firstOrFail();

        return new KitResource($kit);
    }

    public function categories()
    {
        return response()->json(\App\Models\Category::where('is_active', true)->get());
    }
}
