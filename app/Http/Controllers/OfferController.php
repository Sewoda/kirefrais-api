<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\OfferSubscription;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    /**
     * GET /api/offers
     * Returns all active offers with their subscriptions.
     */
    public function index()
    {
        $offers = Offer::where('is_active', true)
            ->with(['subscriptions' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        return response()->json($offers);
    }

    /**
     * GET /api/offers/{slug}
     * Returns a single offer with subscriptions.
     */
    public function show(string $slug)
    {
        $offer = Offer::where('slug', $slug)
            ->where('is_active', true)
            ->with(['subscriptions' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }])
            ->firstOrFail();

        return response()->json($offer);
    }

    /**
     * GET /api/offers/{slug}/subscriptions
     * Returns subscriptions for a specific offer.
     */
    public function subscriptions(string $slug)
    {
        $offer = Offer::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $subscriptions = $offer->subscriptions()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'offer' => $offer,
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * POST /api/subscriptions/pay (overrides old pay method)
     * Initiate payment for an offer subscription.
     */
    public function pay(Request $request)
    {
        $validated = $request->validate([
            'offer_subscription_id' => 'required|exists:offer_subscriptions,id',
        ]);

        $user = $request->user();
        $sub = OfferSubscription::with('offer')->findOrFail($validated['offer_subscription_id']);

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.leekpay.secret_key'),
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ])->post(config('services.leekpay.base_url') . '/checkout', [
            'amount'         => $sub->price,
            'currency'       => 'XOF',
            'description'    => "Abonnement Kirefrais - {$sub->offer->name} / {$sub->name} ({$sub->meals_per_week} repas/sem)",
            'return_url'     => env('FRONTEND_URL', 'https://kirefrais.netlify.app') . '/profil/abonnements',
            'customer_email' => $user->email,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return response()->json([
                'payment_url'    => $data['data']['payment_url'],
                'transaction_id' => $data['data']['payment_id'],
            ]);
        }

        return response()->json([
            'message' => "Erreur lors de l'initiation du paiement.",
            'error'   => $response->json(),
        ], 400);
    }
}
