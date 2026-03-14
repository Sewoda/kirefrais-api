<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->subscriptions()->with('kit')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'meal_kit_id'        => 'required|exists:meal_kits,id',
            'address_id'         => 'required|exists:addresses,id',
            'portions'           => 'required|integer|in:1,2,4',
            'frequency'          => 'required|in:weekly,biweekly,monthly',
            'delivery_slot'      => 'required|in:morning,afternoon,evening',
            'next_delivery_date' => 'required|date|after:today',
        ]);

        $subscription = $request->user()->subscriptions()->create($validated);

        return response()->json([
            'message' => 'Abonnement créé avec succès.',
            'subscription' => $subscription
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $subscription = $request->user()->subscriptions()->findOrFail($id);
        
        $validated = $request->validate([
            'portions'           => 'integer|in:1,2,4',
            'frequency'          => 'in:weekly,biweekly,monthly',
            'delivery_slot'      => 'in:morning,afternoon,evening',
            'next_delivery_date' => 'date',
            'status'             => 'in:active,paused,cancelled'
        ]);

        $subscription->update($validated);

        return response()->json([
            'message' => 'Abonnement mis à jour.',
            'subscription' => $subscription
        ]);
    }

    public function pause(Request $request, $id)
    {
        $subscription = $request->user()->subscriptions()->findOrFail($id);
        $subscription->update([
            'status' => 'paused',
            'pause_weeks' => $request->weeks ?? 1
        ]);

        return response()->json(['message' => 'Abonnement mis en pause.']);
    }

    public function destroy(Request $request, $id)
    {
        $subscription = $request->user()->subscriptions()->findOrFail($id);
        $subscription->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Abonnement annulé.']);
    }
}
