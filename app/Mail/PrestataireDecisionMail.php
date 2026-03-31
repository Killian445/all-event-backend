<?php

namespace App\Mail;

use App\Models\Prestataire;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PrestataireDecisionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Prestataire $prestataire,
        public string $statut,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->statut === 'valide'
                ? 'Votre compte prestataire est validé'
                : 'Mise à jour de votre compte prestataire',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.prestataire-decision',
        );
    }
}

