<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use LogicException;
use NotificationChannels\Telegram\TelegramChannel;

final readonly class NotificationChannelMapper
{
    public const string EMAIL = 'email';
    public const string TELEGRAM = 'telegram';

    public static function map(string $code): string
    {
        return match ($code) {
            'email' => 'mail',
            'telegram' => TelegramChannel::class,
            default => throw new LogicException("Unsupported channel [$code]"),
        };
    }
}
