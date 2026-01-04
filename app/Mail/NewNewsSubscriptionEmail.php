<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewNewsSubscriptionEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public array $value, public string $unsubscribeUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: env('APP_NAME') . '. ' . __('emails.new_news_subscription'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.new_news_subscription');
    }

    public function attachments(): array
    {
        return [];
    }
}
