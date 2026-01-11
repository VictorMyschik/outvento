<?php

declare(strict_types=1);

namespace App\Services\User\Enum;

enum Gender: int
{
    case MALE = 0;
    case FEMALE = 1;

    public function getLabel(): string
    {
        return match ($this) {
            self::MALE => 'Мужской',
            self::FEMALE => 'Женский',
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