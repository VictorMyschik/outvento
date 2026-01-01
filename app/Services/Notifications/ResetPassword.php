<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPassword extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $text
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->view('mail.account.reset_password', ['text' => $this->text]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
