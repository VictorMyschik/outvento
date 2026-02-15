<?php

declare(strict_types=1);

namespace App\Services\Promo\Enum;

enum PromoSource: string
{
    case Footer = 'footer';
    case Admin = 'admin';

    public function getLabel(): string
    {
        return match ($this) {
            self::Footer => 'Footer',
            self::Admin => 'Admin',
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
