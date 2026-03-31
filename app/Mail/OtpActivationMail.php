<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $otpCode,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Activation de votre compte ALL EVENT',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp-activation',
        );
    }
}

