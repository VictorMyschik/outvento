<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyRegistrationCode extends Notification implements ShouldQueue
{
    use Queueable;

    public const string KEY = 'verify_registration_code';

    public function __construct(private readonly string $code, private readonly int $expireMinutes) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->view('emails.verify_email_code', ['code' => $this->code, 'expireMinutes' => $this->expireMinutes]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
