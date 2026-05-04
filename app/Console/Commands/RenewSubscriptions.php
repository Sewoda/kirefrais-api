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
        $this->info('🚀 Démarrage du renouvellement des abonnements...');

        // On récupère les abonnements actifs dont la date de livraison prévue est passée ou aujourd'hui
        $subscriptions = Subscription::where('status', 'active')
            ->where('next_delivery_date', '<=', now()->endOfDay())
            ->get();

        $count = 0;
        foreach ($subscriptions as $sub) {
            $currentDate = Carbon::parse($sub->next_delivery_date);
            
            // Calculer la prochaine date selon la fréquence
            $nextDate = match($sub->frequency) {
                'weekly'   => $currentDate->addWeek(),
                'biweekly' => $currentDate->addWeeks(2),
                'monthly'  => $currentDate->addMonth(),
                default    => $currentDate->addWeek(),
            };

            $sub->next_delivery_date = $nextDate;
            $sub->save();

            $this->line("✅ Abonnement #{$sub->id} (User: {$sub->user_id}) -> Prochaine livraison : {$nextDate->format('d/m/Y')}");
            $count++;
        }

        $this->info("✨ Fin du traitement. {$count} abonnements mis à jour.");
    }
}
