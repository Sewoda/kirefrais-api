<?php
// app/Http/Controllers/Admin/AdminCategoryController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCategoryController extends Controller
{
    public function index()
    {
        return response()->json(Category::withCount('kits')->latest()->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:categories,name',
            'icon' => 'nullable|string|max:50',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'icon' => $request->icon ?? 'heroicons:tag',
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Catégorie créée avec succès.',
            'category' => $category
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:50|unique:categories,name,' . $id,
            'icon' => 'nullable|string|max:50',
        ]);

        $data = $request->only('name', 'icon', 'is_active');
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return response()->json([
            'message' => 'Catégorie mise à jour.',
            'category' => $category
        ]);
    }

    public function destroy(int $id)
    {
        $category = Category::findOrFail($id);
        
        if ($category->kits()->count() > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer une catégorie liée à des kits.'
            ], 422);
        }

        $category->delete();
        return response()->json(['message' => 'Catégorie supprimée.']);
    }
}
