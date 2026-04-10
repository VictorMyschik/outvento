<?php

declare(strict_types=1);

namespace App\Jobs\Enum;

enum QueueJob: string
{
    case Default = 'default';
    case Catalog = 'catalog';
    case Images = 'images';

    public static function getSelectList(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $case) => [$case->value => $case->getLabel()])
            ->toArray();
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Default => 'Default',
            self::Catalog => 'Catalog',
            self::Images => 'Images',
        };
    }
}
