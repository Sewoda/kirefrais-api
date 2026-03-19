<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    // ── Initiation du paiement (Simulation pour le MVP) ──────
    public function initiate(Request $request, $orderId)
    {
        $orderId = (int) $orderId;
        $order = Order::where('user_id', $request->user()->id)->findOrFail($orderId);

        // Initiation simulée avec LeekPay
        $transactionId = 'ORD-' . strtoupper(uniqid());
        $paymentUrl = "https://checkout.leekpay.me/pay/" . $transactionId . "?amount=" . $order->total_amount;

        return response()->json([
            'payment_url'    => $paymentUrl,
            'transaction_id' => $transactionId,
            'order_ref'      => $order->reference
        ]);
    }

    // ── Webhook CinetPay (Notifications de paiement) ──────────
    public function webhook(Request $request)
    {
        // On récupère les infos envoyées par CinetPay
        $paymentData = $request->all();
        $orderId     = $request->input('cpm_site_id'); // Exemple de champ
        $status      = $request->input('cpm_result');  // "00" = succès

        $order = Order::where('reference', $orderId)->first();

        if ($order) {
            if ($status == "00") {
                $order->update([
                    'payment_status'    => 'paid',
                    'status'            => 'paid',
                    'payment_reference' => $request->input('cpm_trans_id')
                ]);

                Log::info("Paiement réussi pour commande : " . $order->reference);
            } else {
                $order->update(['payment_status' => 'failed']);
                Log::warning("Échec paiement pour commande : " . $order->reference);
            }
        }

        return response()->json(['status' => 'acknowledged']);
    }
}
