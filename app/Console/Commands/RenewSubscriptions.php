<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use Carbon\Carbon;

class RenewSubscriptions extends Command
{
    protected $signature = 'subscriptions:renew';
    protected $description = 'Renew active subscriptions for the next cycle and update delivery dates';

    public function handle()
    {
        $this->info('🚀 Démarrage de la gestion des abonnements...');

        // 1. EXPIRATION : On expire ceux qui ont dépassé leur date
        $expiredCount = Subscription::where('status', 'active')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);
            
        if ($expiredCount > 0) {
            $this->warn("⚠️ {$expiredCount} abonnements ont expiré.");
        }

        // 2. NOTIFICATION : On prévient ceux qui expirent dans moins de 24h
        $expiringSoon = Subscription::where('status', 'active')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<', now()->addDay())
            ->get();

        foreach ($expiringSoon as $sub) {
            try {
                \Illuminate\Support\Facades\Mail::to($sub->user->email)
                    ->send(new \App\Mail\SubscriptionExpiring($sub->user, $sub));
                $this->line("📧 Email d'expiration envoyé à : {$sub->user->email}");
            } catch (\Exception $e) {
                $this->error("❌ Erreur email pour User {$sub->user_id}: " . $e->getMessage());
            }
        }

        // 3. RENOUVELLEMENT DE CYCLE (Date de livraison)
        $toUpdate = Subscription::where('status', 'active')
            ->where('next_delivery_date', '<=', now()->endOfDay())
            ->get();

        $renewCount = 0;
        foreach ($toUpdate as $sub) {
            $currentDate = Carbon::parse($sub->next_delivery_date);
            
            $nextDate = match($sub->frequency) {
                'weekly'   => $currentDate->addWeek(),
                'biweekly' => $currentDate->addWeeks(2),
                'monthly'  => $currentDate->addMonth(),
                default    => $currentDate->addWeek(),
            };

            $sub->next_delivery_date = $nextDate;
            $sub->save();

            $this->line("✅ Cycle mis à jour #{$sub->id} -> Prochaine livraison : {$nextDate->format('d/m/Y')}");
            $renewCount++;
        }

        $this->info("✨ Fin du traitement. {$renewCount} cycles mis à jour.");
    }
}
