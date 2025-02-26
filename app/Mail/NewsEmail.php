<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public array $value, public string $unsubscribeUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Eltex. Новостная рассылка',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.mailing_news');
    }

    public function attachments(): array
    {
        return [];
    }
}
