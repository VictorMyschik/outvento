<?php

declare(strict_types=1);

namespace App\Services\Image\Enum;

enum Size: string
{
    case Preview = 'preview';
    case Medium = 'medium';
    case Large = 'large';
    case Original = 'original';

    public static function getSelectList(): array
    {
        return array_combine(
            array_map(fn($enum) => $enum->value, self::cases()),
            array_map(fn($enum) => $enum->getLabel(), self::cases())
        );
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Preview => 'Preview',
            self::Medium => 'Medium',
            self::Large => 'Large',
            self::Original => 'Original',
        };
    }
}
