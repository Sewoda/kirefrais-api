<?php
// app/Http/Controllers/Admin/AdminReviewController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Review, MealKit};
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['user:id,name', 'kit:id,name']);

        if ($request->filled('is_approved')) {
            $query->where('is_approved', $request->is_approved);
        }
        if ($request->rating) {
            $query->where('rating', $request->rating);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function approve(int $id)
    {
        $review = Review::findOrFail($id);
        $review->update(['is_approved' => true]);
        $this->recalculateKitRating($review->meal_kit_id);
        return response()->json(['message' => 'Avis approuvé.']);
    }

    public function reject(int $id)
    {
        $review = Review::findOrFail($id);
        $review->update(['is_approved' => false]);
        $this->recalculateKitRating($review->meal_kit_id);
        return response()->json(['message' => 'Avis rejeté.']);
    }

    public function destroy(int $id)
    {
        $review = Review::findOrFail($id);
        $kitId  = $review->meal_kit_id;
        $review->delete();
        $this->recalculateKitRating($kitId);
        return response()->json(['message' => 'Avis supprimé.']);
    }

    private function recalculateKitRating(int $kitId): void
    {
        $kit = MealKit::findOrFail($kitId);
        $approved = $kit->approvedReviews();
        $kit->update([
            'rating_avg'   => $approved->avg('rating') ?? 0,
            'rating_count' => $approved->count(),
        ]);
    }
}
