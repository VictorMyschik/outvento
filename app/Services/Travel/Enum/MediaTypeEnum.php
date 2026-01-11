<?php

declare(strict_types=1);

namespace App\Services\Travel\Enum;

enum MediaTypeEnum: int
{
    case IMAGE = 0;
    case VIDEO = 1;

    public function getLabel(): string
    {
        return match ($this) {
            self::IMAGE => 'image',
            self::VIDEO => 'video',
        };
    }
}
