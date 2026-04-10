<?php

declare(strict_types=1);

namespace App\Services\Albums\Enum;

enum Icon: int
{
    case Heart = 1;
    //case Like = 2;
    case Fire = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::Heart => 'Heart',
            //self::Like => 'Like',
            self::Fire => 'Fire',
        };
    }

    public function getCode(): string
    {
        return match ($this) {
            self::Heart => 'heart',
            //self::Like => 'like',
            self::Fire => 'fire',
        };
    }

    public static function getSelectList(): array
    {
        return array_combine(
            array_map(fn($enum) => $enum->value, self::cases()),
            array_map(fn($enum) => $enum->getLabel(), self::cases())
        );
    }
    public static function getCodeList(): array
    {
        return array_combine(
            array_map(fn($enum) => $enum->value, self::cases()),
            array_map(fn($enum) => $enum->getCode(), self::cases())
        );
    }
}