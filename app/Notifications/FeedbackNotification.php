<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Services\Forms\DTO\FormFeedbackDTO;
use App\Services\Notifications\Enum\ServiceEvent;
use App\Services\Notifications\NotificationRecipientInterface;
use App\Services\Telegram\TelegramMarkdown;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class FeedbackNotification extends Notification
{
    use Queueable;

    public array $data;
    public const string KEY = ServiceEvent::Feedback->value;

    public function __construct(public FormFeedbackDTO $dto) {}

    public function via(NotificationRecipientInterface $notifiable): array
    {
        return $notifiable->notificationChannelsFor(self::class);
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)->view('emails.feedback', ['dto' => $this->dto]);
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        $lines = [];

        $lines[] = 'New Feedback';
        $lines[] = '';
        $lines[] = 'Name: ' . $this->dto->name;
        $lines[] = TelegramMarkdown::escape('Email: ' . $this->dto->email);
        $lines[] = 'Auth ID: ' . ($this->dto->userId ? : 'No');


        return TelegramMessage::create()
            ->content(implode("\n", $lines))
            ->options([
                'parse_mode'               => 'MarkdownV2',
                'disable_web_page_preview' => true,
            ]);
    }
}
