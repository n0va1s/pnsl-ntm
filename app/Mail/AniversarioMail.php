<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AniversarioMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly array $data) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Feliz Aniversário da PNSL 🎂',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.aniversario',
            with: [
                'nome' => $this->data['nome'],
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
