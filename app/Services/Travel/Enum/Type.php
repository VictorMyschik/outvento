<?php

declare(strict_types=1);

namespace App\Services\Travel\Enum;

enum Type: int
{
    case cycling = 1;
    case MountainCampaign = 2;
    case Hiking = 3;
    case Kayaks = 4;
    case MountainClimbing = 5;

    public function getLabel(): string
    {
        return __('enums.travel_type.' . $this->name);
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