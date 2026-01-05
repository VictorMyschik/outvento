<?php

declare(strict_types=1);

namespace App\Services\Newsletter\Enum;

enum RelationMediaType: string
{
    case NewsLogo = 'news_logo';
    case ConstructorBlockIcon = 'constructor_block_icon';
    case ConstructorSlider = 'constructor_slider';

    public function getLabel(): string
    {
        return match ($this) {
            self::NewsLogo => 'News Logo',
            self::ConstructorBlockIcon => 'Constructor Block Icon',
            self::ConstructorSlider => 'Constructor Slider',
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