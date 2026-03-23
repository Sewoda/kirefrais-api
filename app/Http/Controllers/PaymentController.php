<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    // ── Initiation du paiement réel avec LeekPay ──────
    public function initiate(Request $request, $orderId)
    {
        $order = Order::where('user_id', $request->user()->id)->findOrFail($orderId);

        // Appel à l'API LeekPay v1
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'X-API-KEY' => config('services.leekpay.secret_key'),
            'Accept'    => 'application/json',
        ])->post(config('services.leekpay.base_url') . '/payments', [
            'amount'      => (int) $order->total_amount,
            'currency'    => 'XOF',
            'description' => "Commande Kirefrais #" . $order->reference,
            'return_url'  => env('FRONTEND_URL', 'https://kirefrais.netlify.app') . '/checkout/confirmation?order_id=' . $order->id,
            'cancel_url'  => env('FRONTEND_URL', 'https://kirefrais.netlify.app') . '/checkout/paiement',
            'webhook_url' => config('app.url') . '/api/payments/webhook',
            'metadata'    => [
                'order_id'  => $order->id,
                'order_ref' => $order->reference,
                'user_id'   => $request->user()->id
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            // On vérifie si LeekPay a retourné l'URL
            if (isset($data['data']['checkout_url'])) {
                return response()->json([
                    'payment_url'    => $data['data']['checkout_url'],
                    'transaction_id' => $data['data']['id'],
                    'order_ref'      => $order->reference
                ]);
            }
        }

        \Illuminate\Support\Facades\Log::error("Échec initiation LeekPay", [
            'status' => $response->status(),
            'body'   => $response->body()
        ]);

        return response()->json([
            'message' => "Le service de paiement est temporairement indisponible. Veuillez réessayer.",
            'error'   => $response->json()
        ], 400);
    }

    // ── Webhook LeekPay (Notifications de paiement sécurisées) ──────────
    public function webhook(Request $request)
    {
        // 1. Récupérer la signature envoyée par LeekPay
        $signature = $request->header('X-LeekPay-Signature');
        
        // 2. Récupérer le corps brut de la requête (payload JSON)
        $payload = $request->getContent();

        // 3. Calculer la signature attendue avec votre clé publique via config
        $publicKey = config('services.leekpay.public_key');
        $expectedSignature = hash_hmac('sha256', $payload, $publicKey);

        // 4. Comparer les signatures de manière sécurisée
        if (!hash_equals((string)$expectedSignature, (string)$signature)) {
            \Illuminate\Support\Facades\Log::warning("Signature LeekPay invalide détectée. Webhook rejeté.");
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // 5. Analyser les données du paiement
        $data = json_decode($payload, true);
        $event = $data['event'] ?? null;
        $transaction = $data['transaction'] ?? [];

        if ($event === 'payment.success') {
            // Dans votre initiation, nous avons passé 'order_id' dans les metadata.
            // LeekPay renvoie généralement les metadata du paiement.
            $metadata = $transaction['metadata'] ?? [];
            $orderId  = $metadata['order_id'] ?? null;
            $type     = $metadata['type'] ?? 'order';

            if ($type === 'order' && $orderId) {
                $order = Order::find($orderId);
                if ($order) {
                    $order->update([
                        'payment_status'    => 'paid',
                        'status'            => 'paid',
                        'payment_reference' => $transaction['id'] ?? null
                    ]);
                    \Illuminate\Support\Facades\Log::info("Commande #{$orderId} marquée comme payée via Webhook LeekPay.");
                }
            } 
            elseif ($type === 'subscription') {
                \Illuminate\Support\Facades\Log::info("Abonnement réussi via Webhook pour utilisateur #{$metadata['user_id']}");
                // Logique optionnelle pour activer l'abonnement en DB
            }
        }

        return response()->json(['status' => 'OK'], 200);
    }
}
