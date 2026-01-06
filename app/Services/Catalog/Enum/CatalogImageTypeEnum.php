<?php

declare(strict_types=1);

namespace App\Services\Catalog\Enum;

use UnitEnum;

enum CatalogImageTypeEnum: int
{
    case LOGO = 1;
    case PHOTO = 2;

    public static function getValues(): array
    {
        return array_map(fn(UnitEnum $enum) => $enum->value, self::cases());
    }
}
