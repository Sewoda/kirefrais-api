<?php
// app/Http/Controllers/Admin/AdminKitController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{MealKit, Category};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminKitController extends Controller
{
    public function index(Request $request)
    {
        $query = MealKit::with('category');

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $kits = $query->latest()->paginate(15);

        return response()->json($kits);
    }

    public function show(int $id)
    {
        $kit = MealKit::with(['category', 'approvedReviews.user'])->findOrFail($id);
        return response()->json($kit);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'name'         => 'required|string|max:150',
            'description'  => 'required|string',
            'ingredients'  => 'required|string',
            'prep_time'    => 'required|integer|min:1',
            'difficulty'   => 'required|in:easy,medium,hard',
            'calories'     => 'required|integer|min:0',
            'proteins'     => 'required|numeric|min:0',
            'carbs'        => 'required|numeric|min:0',
            'fats'         => 'required|numeric|min:0',
            'fiber'        => 'required|numeric|min:0',
            'price_1p'     => 'required|numeric|min:0',
            'price_2p'     => 'required|numeric|min:0',
            'price_4p'     => 'required|numeric|min:0',
            'is_vegetarian'=> 'boolean',
            'images'       => 'required|array|min:1',
            'images.*'     => 'url',
        ]);

        $kit = MealKit::create($request->all());

        return response()->json([
            'message' => 'Kit créé avec succès.',
            'kit'     => $kit,
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $kit = MealKit::findOrFail($id);

        $request->validate([
            'category_id'  => 'sometimes|exists:categories,id',
            'name'         => 'sometimes|string|max:150',
            'description'  => 'sometimes|string',
            'ingredients'  => 'sometimes|string',
            'prep_time'    => 'sometimes|integer|min:1',
            'difficulty'   => 'sometimes|in:easy,medium,hard',
            'price_1p'     => 'sometimes|numeric|min:0',
            'price_2p'     => 'sometimes|numeric|min:0',
            'price_4p'     => 'sometimes|numeric|min:0',
            'is_vegetarian'=> 'boolean',
        ]);

        $kit->update($request->all());

        return response()->json([
            'message' => 'Kit mis à jour.',
            'kit'     => $kit->fresh('category'),
        ]);
    }

    public function destroy(int $id)
    {
        $kit = MealKit::findOrFail($id);
        $kit->delete();
        return response()->json(['message' => 'Kit supprimé.']);
    }

    public function toggle(int $id)
    {
        $kit = MealKit::findOrFail($id);
        $kit->update(['is_active' => !$kit->is_active]);
        return response()->json([
            'message'   => $kit->is_active ? 'Kit activé.' : 'Kit désactivé.',
            'is_active' => $kit->is_active,
        ]);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        $path = $request->file('image')->store('kits', 'public');
        $url  = Storage::url($path);

        return response()->json(['url' => $url]);
    }
}
