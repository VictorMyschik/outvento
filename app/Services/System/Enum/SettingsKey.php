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


    public function getLabel(): string
    {
        return match ($this) {
            self::AdminEmail => 'Admin Email',
            self::AdminPhone => 'Admin Phone',
            self::NotificationEnabled => 'Notification Service',
            self::AdminTelegram => 'Admin Telegram',
            self::TelegramChannel => 'Telegram Channel',
            self::TelegramBot => 'Telegram Bot',
        };
    }

    public static function getSelectList(): array
    {
        return array_combine(
            array_map(fn($enum) => $enum->value, self::cases()),
            array_map(fn($enum) => $enum->getLabel(), self::cases())
        );
    }
}
