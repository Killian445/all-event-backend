<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class TransactionalMailer
{
    public function send(string $to, Mailable $mailable): void
    {
        $brevoApiKey = config('services.brevo.api_key');

        if (empty($brevoApiKey)) {
            Mail::to($to)->send($mailable);
            return;
        }

        $senderEmail = (string) config('mail.from.address');
        $senderName = (string) config('mail.from.name');
        $subject = $mailable->envelope()->subject ?: (string) config('app.name');
        $htmlContent = $mailable->render();

        $response = Http::timeout(20)
            ->acceptJson()
            ->withHeaders([
                'api-key' => $brevoApiKey,
            ])
            ->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'email' => $senderEmail,
                    'name' => $senderName,
                ],
                'to' => [
                    ['email' => $to],
                ],
                'subject' => $subject,
                'htmlContent' => $htmlContent,
            ]);

        if ($response->failed()) {
            Log::error('Brevo API email send failed.', [
                'to' => $to,
                'subject' => $subject,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('Echec envoi email transactionnel via Brevo API.');
        }
    }
}
