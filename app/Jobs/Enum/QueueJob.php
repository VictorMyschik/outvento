<?php

declare(strict_types=1);

namespace App\Jobs\Enum;

enum QueueJob: string
{
    case Default = 'default';
    case Catalog = 'catalog';

    public static function getSelectList(): array
    {
        return [
            self::Default->value => self::Default->getLabel(),
            self::Catalog->value => self::Catalog->getLabel(),
        ];
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Default => 'Default',
            self::Catalog => 'Catalog',
        };
    }
}
