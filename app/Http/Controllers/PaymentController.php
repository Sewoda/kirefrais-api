<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    // ── Initiation du paiement avec LeekPay ──────
    public function initiate(Request $request, $orderId)
    {
        $order = Order::where("user_id", $request->user()->id)->findOrFail(
            $orderId,
        );

        // Appel à l'API LeekPay
        $response = Http::withHeaders([
            "Authorization" =>
                "Bearer " . config("services.leekpay.secret_key"),
            "Content-Type" => "application/json",
            "Accept" => "application/json",
        ])->post("https://leekpay.fr/api/v1/checkout", [
            "amount" => (int) $order->total_amount,
            "currency" => "XOF",
            "description" => "Commande Kirefrais #" . $order->reference,
            "return_url" =>
                env("FRONTEND_URL", "https://kirefrais.netlify.app") .
                "/checkout/confirmation?order_id=" .
                $order->id,
            "customer_email" => $request->user()->email,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            if (isset($data["data"]["payment_url"])) {
                return response()->json([
                    "payment_url" => $data["data"]["payment_url"],
                    "transaction_id" => $data["data"]["payment_id"],
                    "order_ref" => $order->reference,
                ]);
            }
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
        $response = Http::withHeaders([
            "Authorization" =>
                "Bearer " . config("services.leekpay.secret_key"),
            "Accept" => "application/json",
        ])->get("https://leekpay.fr/api/v1/checkout/" . $paymentId);

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
    public function webhook(Request $request)
    {
        // 1. Récupérer la signature envoyée par LeekPay
        $signature = $request->header("X-LeekPay-Signature");

        // 2. Récupérer le corps brut de la requête
        $payload = $request->getContent();

        // 3. Calculer la signature attendue avec la clé secrète
        $secretKey = config("services.leekpay.secret_key");
        $expectedSignature = hash_hmac("sha256", $payload, $secretKey);

        // 4. Comparer les signatures de manière sécurisée
        if (!hash_equals((string) $expectedSignature, (string) $signature)) {
            Log::warning("Signature LeekPay invalide. Webhook rejeté.");
            return response()->json(["error" => "Invalid signature"], 401);
        }

        // 5. Analyser les données du paiement
        $data = json_decode($payload, true);
        $status = $data["data"]["status"] ?? null;
        $paymentId = $data["data"]["payment_id"] ?? null;

        if ($status === "paid" && $paymentId) {
            // Récupérer l'order lié via le payment_id stocké
            $order = Order::where("payment_reference", $paymentId)->first();

            if ($order) {
                $order->update([
                    "payment_status" => "paid",
                    "status" => "paid",
                    "payment_reference" => $paymentId,
                ]);

                Log::info(
                    "Commande payée via webhook LeekPay : " . $order->reference,
                );
            }
        } elseif (in_array($status, ["failed", "cancelled"])) {
            $order = Order::where("payment_reference", $paymentId)->first();

            if ($order) {
                $order->update(["payment_status" => $status]);
                Log::warning(
                    "Paiement " .
                        $status .
                        " pour commande : " .
                        ($order->reference ?? $paymentId),
                );
            }
        }

        return response()->json(["status" => "OK"], 200);
    }
}
