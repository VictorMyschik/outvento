<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Mail\NewsEmail;
use App\Models\News\News;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class NewsNotification extends Notification
{
    use Queueable;

    public const string KEY = 'new_comment';


    public function __construct(public News $news, public string $unsubscribeUrl) {}

    public function via($notifiable)
    {
        return $notifiable->notificationChannelsFor(self::class);
    }

    public function toMail($notifiable): NewsEmail
    {
        return new NewsEmail(news: $this->news, unsubscribeUrl: $this->unsubscribeUrl);
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        return TelegramMessage::create()->content("📰 {$this->news->title}");
    }
}
