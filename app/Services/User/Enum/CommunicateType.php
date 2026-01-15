<?php

declare(strict_types=1);

namespace App\Services\User\Enum;

enum CommunicateType: int
{
    case Phone = 1;
    case Email = 2;
    case Address = 3;
    case WhatsApp = 4;
    case Telegram = 5;
    case Viber = 6;
    case Link = 7;
    case GeoCoordinate = 8;
    case Other = 9;

    public function getLabel(): string
    {
        return __('communicate_type.' . $this->name);
    }

    public static function getSelectList(): array
    {
        return array_combine(
            array_map(fn($enum) => $enum->value, self::cases()),
            array_map(fn($enum) => $enum->getLabel(), self::cases())
        );
    }
}
