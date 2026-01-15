<?php

declare(strict_types=1);

namespace App\Services\System\Enum;

enum SettingsKey: string
{
    case AdminEmail = 'admin_email';
    case AdminPhone = 'admin_phone';
    case AdminTelegram = 'admin_telegram';
    case NotificationEnabled = 'notification_enabled';
    case TelegramChannel = 'telegram_channel';
    case TelegramBot = 'telegram_bot';

    public static function getSelectList(): array
    {
        return [
            self::AdminEmail->value          => 'Admin Email',
            self::AdminPhone->value          => 'Admin Phone',
            self::NotificationEnabled->value => 'Notification Service',
            self::AdminTelegram->value       => 'Admin Telegram',
            self::TelegramChannel->value     => 'Telegram Channel',
            self::TelegramBot->value         => 'Telegram Bot',
        ];
    }
}
