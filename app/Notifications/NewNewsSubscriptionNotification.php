<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Services\Notifications\Enum\EventType;
use App\Services\Notifications\NotificationRecipientInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewNewsSubscriptionNotification extends Notification
{
    use Queueable;

    public array $data;
    public const string KEY = EventType::NewNewsSubscription->value;

    public function __construct(public string $unsubscribeUrl) {}

    public function via(NotificationRecipientInterface $notifiable): array
    {
        return $notifiable->notificationChannelsFor(self::class);
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)->view('emails.new_news_subscription', ['unsubscribeUrl' => $this->unsubscribeUrl]);
    }
}
