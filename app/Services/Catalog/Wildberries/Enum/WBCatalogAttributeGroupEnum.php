<?php

declare(strict_types=1);

namespace App\Services\Catalog\Wildberries\Enum;

enum WBCatalogAttributeGroupEnum: int
{
    case COLOR = 1;
    case SIZE = 2;
    case CHARACTERISTIC = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::COLOR => 'Цвет',
            self::SIZE => 'Размер',
            self::CHARACTERISTIC => 'Характеристики',
        };
    }

    public static function getSelectList(): array
    {
        return [
            self::COLOR->value          => WBCatalogAttributeGroupEnum::COLOR->getLabel(),
            self::SIZE->value           => WBCatalogAttributeGroupEnum::SIZE->getLabel(),
            self::CHARACTERISTIC->value => WBCatalogAttributeGroupEnum::CHARACTERISTIC->getLabel(),
        ];
    }
}
