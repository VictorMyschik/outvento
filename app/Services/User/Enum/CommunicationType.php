<?php

declare(strict_types=1);

namespace App\Services\User\Enum;

enum CommunicationType: int
{
    case Phone = 1;
    case Email = 2;
    case Address = 3;
    case Whatsapp = 4;
    case Telegram = 5;
    case Viber = 6;
    case Link = 7;
    case Geocoordinates = 8;
    case Other = 9;

    public function getLabel(): string
    {
        return __('enums.communication_types.' . $this->name);
    }

    public static function getSelectList(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $case) => [
                $case->value => $case->getLabel(),
            ])
            ->toArray();
    }
}