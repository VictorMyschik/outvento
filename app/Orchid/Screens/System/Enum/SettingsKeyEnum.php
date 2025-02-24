<?php

declare(strict_types=1);

namespace App\Orchid\Screens\System\Enum;

enum SettingsKeyEnum: string
{
    case Wb_SYSTEM_TOKEN = 'wb_system_token';

    public static function getSelectList(): array
    {
        return [
            self::Wb_SYSTEM_TOKEN->value => 'Токен системы Wildberries',
        ];
    }
}
