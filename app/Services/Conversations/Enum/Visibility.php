<?php

declare(strict_types=1);

namespace App\Services\Conversations\Enum;

enum Visibility: string
{
    case Private = 'private';
    case Searchable = 'searchable';
    case Public = 'public';

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
            self::Private => 'Private',
            self::Searchable => 'Searchable',
            self::Public => 'Public',
        };
    }
}