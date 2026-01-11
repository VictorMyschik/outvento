<?php

declare(strict_types=1);

namespace App\Services\Newsletter\Enum;

enum NewsAdditionalTypeEnum: int
{
    case GOOD = 1;
    case GROUP = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::GOOD => 'Товар',
            self::GROUP => 'Группа',
        };
    }
}
