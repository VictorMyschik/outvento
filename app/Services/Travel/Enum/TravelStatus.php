<?php

declare(strict_types=1);

namespace App\Services\Travel\Enum;

enum TravelStatus: int
{
    case Draft = 0;
    case Active = 1;
    case Archived = 2;
    case Deleted = 3;

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
        return __('enums.travel_status.' . $this->name);
    }
}
