<?php

declare(strict_types=1);

namespace App\Jobs\Enum;

enum QueueJob: string
{
    case DEFAULT = 'default';

    public static function getSelectList(): array
    {
        return [
            self::DEFAULT->value => self::DEFAULT->getLabel(),
        ];
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::DEFAULT => 'Default',
        };
    }
}
