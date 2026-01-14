<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Services\Notifications\NotificationRecipientInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class NewsNotification extends Notification
{
    use Queueable;

    public array $data;
    public const string KEY = 'new_comment';

    public function __construct(public array $newsList, public string $unsubscribeUrl) {}

    public function via(NotificationRecipientInterface $notifiable): array
    {
        return $notifiable->notificationChannelsFor(self::class);
    }

    public function toMail($notifiable): MailMessage
    {
        $newsDataList = [];
        foreach ($this->newsList as $newsList) {
            $newsDataList[] = [
                'title' => $newsList->title,
                'url'   => $newsList->getUrl(),
            ];
        }

        return (new MailMessage)->view('emails.news_digest', [
            'newsDataList'   => $newsDataList,
            'unsubscribeUrl' => $this->unsubscribeUrl
        ]);
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        return TelegramMessage::create()->content('');
    }
}
