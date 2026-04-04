<?php

declare(strict_types=1);

namespace App\Services\Conversations\Enum;

enum Status: string
{
    case Active = 'active';
    case Invited = 'invited';
    case Requested = 'requested';
    case Banned = 'banned';
    case Left = 'left';

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
            self::Active => 'Active',
            self::Invited => 'Invited',
            self::Requested => 'Requested',
            self::Banned => 'Banned',
            self::Left => 'Left',
        };
    }
}
