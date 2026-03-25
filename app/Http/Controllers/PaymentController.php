<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    // ── Client HTTP LeekPay partagé ──────────────
    private function leekpayClient(): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::timeout(30)->withHeaders([
            "Authorization" =>
                "Bearer " . config("services.leekpay.secret_key"),
            "Content-Type" => "application/json",
            "Accept" => "application/json",
        ]);

        // Désactiver la vérification SSL si nécessaire (ex: Laravel Cloud)
        if (!config("services.leekpay.verify_ssl", true)) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    // ── Initiation du paiement avec LeekPay ──────
    public function initiate(Request $request, $orderId)
    {
        $order = Order::where("user_id", $request->user()->id)->findOrFail(
            $orderId,
        );

        try {
            $response = $this->leekpayClient()->post(
                config("services.leekpay.base_url") . "/checkout",
                [
                    "amount" => (int) $order->total_amount,
                    "currency" => "XOF",
                    "description" => "Commande Kirefrais #" . $order->reference,
                    "return_url" =>
                        env("FRONTEND_URL") .
                        "/checkout/confirmation?order_id=" .
                        $order->id,
                    "customer_email" => $request->user()->email,
                    "webhook_url"    => env("APP_URL") . "/api/webhook/leekpay",
                    
                ],
                
            );
        } catch (ConnectionException $e) {
            Log::error("LeekPay : impossible de se connecter", [
                "url" => config("services.leekpay.base_url") . "/checkout",
                "message" => $e->getMessage(),
            ]);

            return response()->json(
                [
                    "message" =>
                        "Le service de paiement est inaccessible. Veuillez réessayer dans quelques instants.",
                ],
                503,
            );
        }

        if ($response->successful()) {
            $data = $response->json();

            // Log la réponse complète pour déboguer
            Log::info("Réponse LeekPay", ["data" => $data]);

            if (isset($data["data"]["payment_url"])) {
                $paymentId = $data["data"]["payment_id"] ?? null;

                // Sauvegarder la référence du paiement sur la commande
                if ($paymentId) {
                    $order->update(["payment_reference" => $paymentId]);
                }

                return response()->json([
                    "payment_url" => $data["data"]["payment_url"],
                    "transaction_id" => $paymentId,
                    "order_ref" => $order->reference,
                ]);
            }

            // payment_url absent dans la réponse
            Log::warning("LeekPay : payment_url absent dans la réponse", [
                "data" => $data,
            ]);
        }

        Log::error("Échec initiation LeekPay", [
            "status" => $response->status(),
            "body" => $response->body(),
        ]);

        return response()->json(
            [
                "message" =>
                    "Le service de paiement est temporairement indisponible. Veuillez réessayer.",
                "error" => $response->json(),
            ],
            400,
        );
    }

    // ── Vérifier le statut d'un paiement ──────────
    public function verify(Request $request, $paymentId)
    {
        try {
            $response = $this->leekpayClient()->get(
                config("services.leekpay.base_url") . "/checkout/" . $paymentId,
            );
        } catch (ConnectionException $e) {
            Log::error(
                "LeekPay : impossible de vérifier le paiement (connexion)",
                [
                    "payment_id" => $paymentId,
                    "message" => $e->getMessage(),
                ],
            );

            return response()->json(
                [
                    "message" =>
                        "Le service de paiement est inaccessible. Veuillez réessayer.",
                ],
                503,
            );
        }

        if ($response->successful()) {
            return response()->json($response->json());
        }

        Log::error("Échec vérification LeekPay", [
            "payment_id" => $paymentId,
            "status" => $response->status(),
            "body" => $response->body(),
        ]);

        return response()->json(
            [
                "message" => "Impossible de vérifier le paiement.",
                "error" => $response->json(),
            ],
            400,
        );
    }

    // ── Webhook LeekPay (Notifications de paiement) ──────────
    // public function webhook(Request $request)
    // {
    //     // 1. Récupérer la signature envoyée par LeekPay
    //     $signature = $request->header("X-LeekPay-Signature");

    //     // 2. Récupérer le corps brut de la requête
    //     $payload = $request->getContent();

    //     // 3. Calculer la signature attendue avec la clé secrète
    //     $secretKey = config("services.leekpay.secret_key");
    //     $expectedSignature = hash_hmac("sha256", $payload, $secretKey);

    //     // 4. Comparer les signatures de manière sécurisée
    //     if (!hash_equals((string) $expectedSignature, (string) $signature)) {
    //         Log::warning("Signature LeekPay invalide. Webhook rejeté.");
    //         return response()->json(["error" => "Invalid signature"], 401);
    //     }

    //     // 5. Analyser les données du paiement
    //     $data = json_decode($payload, true);

    //     if (!$data || !isset($data["data"])) {
    //         Log::warning("Payload webhook LeekPay invalide ou vide.");
    //         return response()->json(["error" => "Invalid payload"], 400);
    //     }

    //     $status = $data["data"]["status"] ?? null;
    //     $paymentId = $data["data"]["payment_id"] ?? null;

    //     if ($status === "paid" && $paymentId) {
    //         $order = Order::where("payment_reference", $paymentId)->first();

    //         if ($order) {
    //             $order->update([
    //                 "payment_status" => "paid",
    //                 "status" => "paid",
    //                 "payment_reference" => $paymentId,
    //             ]);

    //             Log::info(
    //                 "Commande payée via webhook LeekPay : " . $order->reference,
    //             );
    //         }
    //     } elseif (in_array($status, ["failed", "cancelled"])) {
    //         $order = Order::where("payment_reference", $paymentId)->first();

    //         if ($order) {
    //             $order->update(["payment_status" => $status]);
    //             Log::warning(
    //                 "Paiement " .
    //                     $status .
    //                     " pour commande : " .
    //                     ($order->reference ?? $paymentId),
    //             );
    //         }
    //     }

    //     return response()->json(["status" => "OK"], 200);
    // }


    // ── Webhook LeekPay ──────────────────────────────
    public function webhook(Request $request)
    {
        // 1. Récupérer la signature et le payload brut
        $signature = $request->header("X-LeekPay-Signature");
        $payload   = $request->getContent();

        // 2. Vérifier avec la CLÉ PUBLIQUE (pk_live_xxx)
        $publicKey         = config("services.leekpay.public_key");
        $expectedSignature = hash_hmac("sha256", $payload, $publicKey);

        if (!hash_equals((string) $expectedSignature, (string) $signature)) {
            Log::warning("LeekPay Webhook : signature invalide", [
                "received" => $signature,
                "expected" => $expectedSignature,
            ]);
            return response()->json(["error" => "Invalid signature"], 401);
        }

        // 3. Parser le payload
        $data = json_decode($payload, true);

        if (!$data || !isset($data["transaction"])) {
            Log::warning("LeekPay Webhook : payload invalide", ["raw" => $payload]);
            return response()->json(["error" => "Invalid payload"], 400);
        }

        $event       = $data["event"] ?? null;          // "payment.success"
        $transaction = $data["transaction"];
        $paymentId   = (string) ($transaction["id"] ?? null);
        $status      = $transaction["status"] ?? null;  // "completed"

        Log::info("LeekPay Webhook reçu", [
            "event"      => $event,
            "payment_id" => $paymentId,
            "status"     => $status,
        ]);

        // 4. Retrouver la commande
        $order = Order::where("payment_reference", $paymentId)->first();

        if (!$order) {
            Log::warning("LeekPay Webhook : commande introuvable", [
                "payment_id" => $paymentId
            ]);
            // On retourne 200 quand même (évite les retries inutiles)
            return response()->json(["status" => "order_not_found"], 200);
        }

        // 5. Idempotence — éviter le double traitement
        if ($order->payment_status === "paid") {
            Log::info("LeekPay Webhook : déjà traité", ["order" => $order->reference]);
            return response()->json(["status" => "already_processed"], 200);
        }

        // 6. Mettre à jour selon le statut
        match ($event) {
            "payment.success" => $this->markAsPaid($order, $transaction),
            "payment.failed"  => $order->update(["payment_status" => "failed"]),
            "payment.cancelled" => $order->update(["payment_status" => "cancelled"]),
            default => Log::info("LeekPay Webhook : événement non géré : " . $event)
        };

        return response()->json(["status" => "OK"], 200);
    }

    private function markAsPaid(Order $order, array $transaction): void
    {
        $order->update([
            "payment_status" => "paid",
            "status"         => "paid",
            "paid_at"        => now(),
            "paid_amount"    => $transaction["amount"] ?? null,
        ]);

        Log::info("✅ Commande payée", [
            "order"    => $order->reference,
            "amount"   => $transaction["amount"],
            "customer" => $transaction["customer_email"] ?? null,
        ]);

        // Envoyer email ici si besoin
        // Mail::to($transaction['customer_email'])->send(new PaymentConfirmed($order));
    }
}
