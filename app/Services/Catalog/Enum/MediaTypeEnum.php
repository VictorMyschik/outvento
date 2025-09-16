<?php

declare(strict_types=1);

namespace App\Services\Catalog\Enum;

use UnitEnum;

enum MediaTypeEnum: int
{
    case IMAGE = 0;
    case VIDEO = 1;

    public static function getValues(): array
    {
        return array_map(fn(UnitEnum $enum) => $enum->value, self::cases());
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::IMAGE => 'image',
            self::VIDEO => 'video',
        };
    }
}
