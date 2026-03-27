<?php

declare(strict_types=1);

namespace App\Services\Notifications\Resolvers;

use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\User\Enum\CommunicationType;

final class CommunicationChannelSupportResolver
{
    /**
     * @param CommunicationType $type
     * @return NotificationChannel|null
     */
    public static function fromCommunicationType(CommunicationType $type): ?NotificationChannel
    {
        return match ($type) {
            CommunicationType::Email => NotificationChannel::Email,
            CommunicationType::Telegram => NotificationChannel::Telegram,
            default => null,
        };
    }

    public static function fromNotificationChannelType(NotificationChannel $type): ?CommunicationType
    {
        return match ($type) {
            NotificationChannel::Email => CommunicationType::Email,
            NotificationChannel::Telegram => CommunicationType::Telegram,
            default => null,
        };
    }
}
