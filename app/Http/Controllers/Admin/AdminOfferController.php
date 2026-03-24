<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\OfferSubscription;
use Illuminate\Http\Request;

class AdminOfferController extends Controller
{
    /**
     * List all offers with their subscriptions.
     */
    public function index()
    {
        $offers = Offer::with(['subscriptions' => function ($query) {
            $query->orderBy('sort_order')->orderBy('meals_per_week');
        }])->orderBy('sort_order')->get();

        return response()->json($offers);
    }

    /**
     * Show a single offer details.
     */
    public function show(int $id)
    {
        $offer = Offer::with('subscriptions')->findOrFail($id);
        return response()->json($offer);
    }

    /**
     * Create a new offer.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'persons' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $offer = Offer::create($request->all());

        return response()->json([
            'message' => 'Offre créée avec succès.',
            'offer' => $offer
        ], 201);
    }

    /**
     * Update an offer.
     */
    public function update(Request $request, int $id)
    {
        $offer = Offer::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:100',
            'persons' => 'sometimes|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $offer->update($request->all());

        return response()->json([
            'message' => 'Offre mise à jour.',
            'offer' => $offer
        ]);
    }

    /**
     * Toggle offer active status.
     */
    public function toggle(int $id)
    {
        $offer = Offer::findOrFail($id);
        $offer->update(['is_active' => !$offer->is_active]);

        return response()->json([
            'message' => $offer->is_active ? 'Offre activée.' : 'Offre désactivée.',
            'is_active' => $offer->is_active
        ]);
    }

    /**
     * --- SUBSCRIPTIONS (FORMULES) ---
     */

    /**
     * Add a subscription to an offer.
     */
    public function storeSubscription(Request $request, int $offerId)
    {
        $offer = Offer::findOrFail($offerId);

        $request->validate([
            'name' => 'required|string|max:100',
            'meals_per_week' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
            'popular' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $sub = $offer->subscriptions()->create($request->all());

        return response()->json([
            'message' => 'Abonnement ajouté avec succès.',
            'subscription' => $sub
        ], 201);
    }

    /**
     * Update a subscription.
     */
    public function updateSubscription(Request $request, int $subId)
    {
        $sub = OfferSubscription::findOrFail($subId);

        $request->validate([
            'name' => 'sometimes|string|max:100',
            'meals_per_week' => 'sometimes|integer|min:1',
            'price' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
            'popular' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $sub->update($request->all());

        return response()->json([
            'message' => 'Abonnement mis à jour.',
            'subscription' => $sub
        ]);
    }

    /**
     * Delete an offer (only if it has no active user subscriptions? Or simple delete).
     */
    public function destroy(int $id)
    {
        $offer = Offer::findOrFail($id);
        $offer->delete(); // This will cascade if implemented in migration, or we manualy check
        return response()->json(['message' => 'Offre supprimée.']);
    }

    /**
     * Delete a subscription formula.
     */
    public function destroySubscription(int $subId)
    {
        $sub = OfferSubscription::findOrFail($subId);
        $sub->delete();
        return response()->json(['message' => 'Formule d\'abonnement supprimée.']);
    }
}
