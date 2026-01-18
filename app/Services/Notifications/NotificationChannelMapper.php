<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use LogicException;
use NotificationChannels\Telegram\TelegramChannel;

final class NotificationChannelMapper
{
    public static function map(string $code): string
    {
        return match ($code) {
            'email' => 'mail',
            'telegram' => TelegramChannel::class,
            'sms' => 'vonage',
            default => throw new LogicException("Unsupported channel [$code]"),
        };
    }
}
