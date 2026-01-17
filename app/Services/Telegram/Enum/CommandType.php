<?php

declare(strict_types=1);

namespace App\Services\Telegram\Enum;

enum CommandType: string
{
    case Start = '/start';
    case HELP = 'help';
    case CLEAR = 'clear';

    public static function tryFromCode(string $message): ?self
    {
        return match ($message) {
            self::Start->value => self::Start,
            self::HELP->value => self::HELP,
            self::CLEAR->value => self::CLEAR,
            default => null,
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Start => 'Start',
            self::HELP => 'Help',
            self::CLEAR => 'Clear all subscriptions',
        };
    }

    public static function getSelectList(): array
    {
        return [
            self::Start->value => self::Start->getLabel(),
            self::HELP->value  => self::HELP->getLabel(),
            self::CLEAR->value => self::CLEAR->getLabel(),
        ];
    }
}
