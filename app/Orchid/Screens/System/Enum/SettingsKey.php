<?php

declare(strict_types=1);

namespace App\Orchid\Screens\System\Enum;

enum SettingsKey: string
{
    case EMAIL_SERVICE = 'email_service';
    case ADMIN_EMAIL = 'admin_email';

    public static function getSelectList(): array
    {
        return [
            self::EMAIL_SERVICE->value => 'Email Service',
            self::ADMIN_EMAIL->value => 'Admin Email',
        ];
    }
}
