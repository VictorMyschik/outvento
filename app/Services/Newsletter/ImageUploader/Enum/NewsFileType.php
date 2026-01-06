<?php

declare(strict_types=1);

namespace App\Services\Newsletter\ImageUploader\Enum;

enum NewsFileType: int
{
    case Image = 1;
    case Video = 2;
    case File = 3;
    case Archive = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::Image => 'Image',
            self::Video => 'Video',
            self::File => 'File',
            self::Archive => 'Archive',
        };
    }

    public static function getSelectList(): array
    {
        return array_combine(
            array_map(fn($enum) => $enum->value, self::cases()),
            array_map(fn($enum) => $enum->getLabel(), self::cases())
        );
    }
}