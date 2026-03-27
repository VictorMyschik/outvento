<?php

declare(strict_types=1);

namespace App\Notifications\Service;

use App\Notifications\Channels\Internal;
use App\Services\Notifications\Enum\ServiceEvent;
use App\Services\Notifications\NotificationRecipientInterface;
use App\Services\Travel\DTO\TravelInviteDto;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TravelInviteNotification extends Notification
{
    use Queueable;

    public array $data;
    public const int KEY = ServiceEvent::Invite->value;

    public function __construct(public TravelInviteDto $dto) {}

    public function via(NotificationRecipientInterface $notifiable): array
    {
        return array_merge($notifiable->notificationChannelsFor(self::class), [Internal::class]);
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)->view('emails.travel_invite', ['dto' => $this->dto])
            ->withSymfonyMessage(function ($message) {
                $message->getHeaders()->addTextHeader('X-Notification-Key', ServiceEvent::Invite->name);
            });
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        $lines = [
            'New Travel Invite',
            'Link: ' . $this->dto->confirmationUrl,
        ];

        return TelegramMessage::create()
            ->content(implode("\n", $lines))
            ->options([
                'parse_mode'               => 'MarkdownV2',
                'disable_web_page_preview' => true,
            ]);
    }

    public function toInternalDatabase($notifiable): array
    {
        $lines = [
            'New Travel Invite',
            'Link: ' . $this->dto->confirmationUrl,
        ];

        return [
            'user_id' => $this->dto->userId,
            'title'   => 'New Travel Invite',
            'message' => implode("\n", $lines),
        ];
    }
}
