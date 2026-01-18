<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Services\Notifications\Enum\EventType;
use App\Services\Notifications\NotificationRecipientInterface;
use App\Services\Telegram\TelegramMarkdown;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class NewsNotification extends Notification
{
    use Queueable;

    public array $data;
    public const string KEY = EventType::News->value;

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
        $lines = [];

        $title = TelegramMarkdown::escape(__('emails.news_digest.title'));
        $intro = TelegramMarkdown::escape(__('emails.news_digest.intro'));

        $lines[] = "📰 *{$title}*";
        $lines[] = '';
        $lines[] = $intro;
        $lines[] = '';

        foreach ($this->newsList as $news) {
            $newsTitle = TelegramMarkdown::escape($news->title);
            $url = 'https://travel.allximik.com';

            $lines[] = "• [{$newsTitle}]({$url})";
            $lines[] = '';
        }

        return TelegramMessage::create()
            ->content(implode("\n", $lines))
            ->options([
                'parse_mode'               => 'MarkdownV2',
                'disable_web_page_preview' => true,
            ]);
    }
}
