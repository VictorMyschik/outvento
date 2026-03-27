<?php

declare(strict_types=1);

namespace App\Services\Travel\Enum;

enum TravelPointType: int
{
    case Start = 1;
    case Stop = 2;
    case Finish = 3;
    case Poi = 4;

    public static function getSelectList(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $case) => [
                $case->value => $case->getLabel(),
            ])
            ->toArray();
    }

    public function getLabel(): string
    {
        return __('enums.travel_point.' . $this->name);
    }
}
