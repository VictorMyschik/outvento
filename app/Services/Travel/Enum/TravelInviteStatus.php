<?php

declare(strict_types=1);

namespace App\Services\Travel\Enum;

enum TravelInviteStatus: int
{
    case Pending = 0;
    case Accepted = 1;
    case Declined = 2;

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
        return __('enums.travel_invite_status.' . $this->name);
    }
}
