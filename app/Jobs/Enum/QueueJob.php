<?php

declare(strict_types=1);

namespace App\Jobs\Enum;

enum QueueJob: string
{
    case Default = 'default';
    case OnlinerCatalog = 'onliner_catalog';

    public static function getSelectList(): array
    {
        return [
            self::Default->value => self::Default->getLabel(),
        ];
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Default => 'Default',
            self::OnlinerCatalog => 'Onliner catalog',
        };
    }
}
