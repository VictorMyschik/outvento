<?php

declare(strict_types=1);

namespace App\Services\Notifications\Enum;

enum PromoEvent: string
{
    case News = 'news';

    public function getLabel(): string
    {
        return match ($this) {
            self::News => 'News',
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
