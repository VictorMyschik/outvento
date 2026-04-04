<?php

declare(strict_types=1);

namespace App\Services\Conversations\Enum;

enum Type: string
{
    case Private = 'private';
    case Group = 'group';
    case Public = 'public';

    public static function getSelectList(): array
    {
        return array_combine(
            array_map(fn($enum) => $enum->value, self::cases()),
            array_map(fn($enum) => $enum->getLabel(), self::cases())
        );
    }

    public static function getSelectGroupList(): array
    {
        return [
            self::Group->value  => self::Group->getLabel(),
            self::Public->value => self::Public->getLabel(),
        ];
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Private => 'Private',
            self::Group => 'Group',
            self::Public => 'Public',
        };
    }
}
