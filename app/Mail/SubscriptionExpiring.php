<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiring extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $subscription;
    public $url;

    public function __construct($user, $subscription)
    {
        $this->user = $user;
        $this->subscription = $subscription;
        $this->url = config('app.frontend_url', 'https://freshkits.tg') . '/profil/abonnements';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre abonnement FreshKits expire bientôt !',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.subscriptions.expiring',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
