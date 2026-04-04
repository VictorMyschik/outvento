<?php

declare(strict_types=1);

namespace App\Services\Conversations\Enum;

enum JoinPolicy: string
{
    case Disable = 'disable';
    case Invite = 'invite';
    case Request = 'request';
    case Open = 'open';

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
            self::Disable => 'Disabled',
            self::Invite => 'Invite Only',
            self::Request => 'Request to Join',
            self::Open => 'Open to All',
        };
    }
}
