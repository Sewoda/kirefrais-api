<x-mail::message>
# Votre abonnement FreshKits expire bientôt !

Bonjour {{ $user->name }},

Votre abonnement actuel pour **{{ $subscription->meals_per_week }} kits par semaine** arrive à sa fin.

Il expirera le **{{ $subscription->expires_at->format('d/m/Y') }}**.

Pour continuer à profiter de vos kits inclus et ne pas payer les frais à l'unité, nous vous invitons à renouveler votre abonnement dès maintenant.

<x-mail::button :url="$url">
Renouveler mon abonnement
</x-mail::button>

Merci de votre fidélité !<br>
L'équipe {{ config('app.name') }}
</x-mail::message>
