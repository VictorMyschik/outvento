<?php

declare(strict_types=1);

namespace App\Services\Travel\Enum;

enum Activity: int
{
    case Cycling = 1;
    case MountainCampaign = 2;
    case Hiking = 3;
    case Kayaks = 4;
    case MountainClimbing = 5;

    public function getLabel(): string
    {
        return __('enums.activities.' . $this->name);
    }

    public static function getSelectList(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [
                $case->value => $case->getLabel(),
            ])
            ->toArray();
    }

    public function getImageUrl(): string
    {
        return '';
    }
}