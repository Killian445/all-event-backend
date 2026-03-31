<?php

namespace App\Mail;

use App\Models\Prestataire;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PrestataireUnderReviewMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Prestataire $prestataire,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre compte prestataire est en vérification',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.prestataire-under-review',
        );
    }
}

