<?php

declare(strict_types=1);

namespace App\Services\User\Enum;

enum Gender: int
{
    case MALE = 1;
    case FEMALE = 2;

    public function getLabel(): string
    {
        return __('enums.gender.' . $this->name);
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
