<?php

declare(strict_types=1);

namespace App\Services\Travel\Enum;

enum UserTravelRole: int
{
    case Owner = 0;
    case Member = 1;

    public function getLabel(): string
    {
        return __('enums.user_travel_role.' . $this->name);
    }

    public static function getSelectList(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [
                $case->value => $case->getLabel(),
            ])
            ->toArray();
    }
}
