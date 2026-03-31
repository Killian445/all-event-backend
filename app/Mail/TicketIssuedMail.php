<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketIssuedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
        public ?string $mapsUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre billet électronique ALL EVENT',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-issued',
        );
    }
}

