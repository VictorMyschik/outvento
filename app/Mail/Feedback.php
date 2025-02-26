<?php

namespace App\Mail;

use App\Models\Forms\Form;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Feedback extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Form $form) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Feedback from ' . env('APP_NAME'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.feedback');
    }

    public function attachments(): array
    {
        return [];
    }
}
