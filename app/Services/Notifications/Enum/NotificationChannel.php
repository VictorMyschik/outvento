<?php

declare(strict_types=1);

namespace App\Services\Notifications\Enum;

enum NotificationChannel: string
{
    case Email = 'mail';
    case Telegram = 'telegram';
    case Internal = 'internal';

    public function getLabel(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::Telegram => 'Telegram',
            self::Internal => 'Internal',
        };
    }

    public static function getSelectOutList(): array
    {
        return [
            self::Email->value => self::Email->getLabel(),
            self::Telegram->value => self::Telegram->getLabel(),
        ];
    }

    public static function getSelectList(): array
    {
        return array_combine(
            array_map(fn($enum) => $enum->value, self::cases()),
            array_map(fn($enum) => $enum->getLabel(), self::cases())
        );
    }
}
