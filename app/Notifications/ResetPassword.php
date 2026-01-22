<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPassword extends Notification implements ShouldQueue
{
    use Queueable;

    public const string KEY = 'reset_password';

    public function __construct(
        public readonly string $url,
        public string          $userLocale,
        public int             $expireMinutes,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->view('emails.reset_password', [
            'url'           => $this->url,
            'expireMinutes' => $this->expireMinutes,
            'locale'        => $this->userLocale,
            't'             => __('emails.reset_password', [], $this->userLocale),
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
