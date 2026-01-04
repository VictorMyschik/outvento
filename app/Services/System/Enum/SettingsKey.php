<?php

declare(strict_types=1);

namespace App\Services\System\Enum;

enum SettingsKey: string
{
    case AdminEmail = 'admin_email';
    case AdminPhone = 'admin_phone';
    case AdminTelegram = 'admin_telegram';
    case EmailService = 'email_service';

    public static function getSelectList(): array
    {
        return [
            self::AdminEmail->value   => 'Admin Email',
            self::AdminPhone->value   => 'Admin Phone',
            self::EmailService->value => 'Email Service',
            self::AdminTelegram->value => 'Admin Telegram',
        ];
    }
}
