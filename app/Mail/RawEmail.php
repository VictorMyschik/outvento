<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RawEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public $subject, public string $body) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: (string)$this->subject,
        );
    }

    public function content(): Content
    {
        return new Content(htmlString: $this->body);
    }

    public function attachments(): array
    {
        return [];
    }
}