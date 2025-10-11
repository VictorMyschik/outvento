<?php

declare(strict_types=1);

namespace App\Services\System\Enum;

enum SettingsKeyEnum: string
{
    case AdminEmail = 'admin_email';
    case EmailService = 'email_service';

    public static function getSelectList(): array
    {
        return [
            self::AdminEmail->value   => 'Admin Email',
            self::EmailService->value => 'Email Service',
        ];
    }
}
