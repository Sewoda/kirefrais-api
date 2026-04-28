<x-mail::message>
# Merci pour votre paiement !

Bonjour,

Nous vous confirmons la réception de votre paiement de **{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA** pour la commande **#{{ $order->reference }}**.

Votre abonnement Kirefrais est désormais actif ! Vous pouvez commencer à profiter de nos délicieux repas.

<x-mail::button :url="config('app.frontend_url', 'https://kirefrais.com') . '/mon-compte'">
Accéder à mon compte
</x-mail::button>

Si vous avez des questions, n'hésitez pas à nous contacter.

À très bientôt,  
L'équipe {{ config('app.name') }}
</x-mail::message>
