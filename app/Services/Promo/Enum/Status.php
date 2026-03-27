<?php

declare(strict_types=1);

namespace App\Services\Promo\Enum;

enum Status: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Revoked = 'revoked';

    public function getLabel(): string
    {
        return __('enums.promo_status.' . $this->name);
    }

    public static function getSelectList(): array
    {
        return array_combine(
            array_map(fn($enum) => $enum->value, self::cases()),
            array_map(fn($enum) => $enum->getLabel(), self::cases())
        );
    }
}