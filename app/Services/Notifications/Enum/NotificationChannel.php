<?php

declare(strict_types=1);

namespace App\Services\Notifications\Enum;

/**
 * Код канала уведомлений совпадает с кодом канала в БД, который используется в таблице communication_types
 * */
enum NotificationChannel: string
{
    case Email = 'mail';
    case Telegram = 'telegram';

    public function getLabel(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::Telegram => 'Telegram',
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
