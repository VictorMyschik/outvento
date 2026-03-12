<?php

declare(strict_types=1);

namespace App\Services\Notifications\Enum;

enum NotificationChannel: string
{
    case Email = 'mail';
    case Telegram = 'telegram';
    case Internal = 'internal';

    public static function getCasesOutList(): array
    {
        return [
            self::Email,
            self::Telegram,
        ];
    }

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
        $out = [];

        foreach (self::getCasesOutList() as $case) {
            $out[$case->value] = $case->getLabel();
        }

        return $out;
    }

    public static function getSelectList(): array
    {
        return array_combine(
            array_map(fn($enum) => $enum->value, self::cases()),
            array_map(fn($enum) => $enum->getLabel(), self::cases())
        );
    }
}
