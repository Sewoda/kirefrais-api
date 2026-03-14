<?php
// app/Http/Controllers/ReviewController.php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\MealKit;
use App\Http\Requests\Review\StoreReviewRequest;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['meal_kit_id' => 'required|exists:meal_kits,id']);
        
        $reviews = Review::where('meal_kit_id', $request->meal_kit_id)
            ->where('is_approved', true)
            ->with('user:id,name,avatar')
            ->latest()
            ->get();

        return response()->json($reviews);
    }

    public function store(StoreReviewRequest $request)
    {
        $review = Review::create([
            'user_id'     => $request->user()->id,
            'meal_kit_id' => $request->meal_kit_id,
            'order_id'    => $request->order_id,
            'rating'      => $request->rating,
            'comment'     => $request->comment,
            'is_approved' => false, // Review modérée
        ]);

        return response()->json([
            'message' => 'Merci pour votre avis ! Il sera visible après modération.',
            'review'  => $review
        ], 201);
    }
}
