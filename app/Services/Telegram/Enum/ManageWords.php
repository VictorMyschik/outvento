<?php

declare(strict_types=1);

namespace App\Services\Telegram\Enum;

enum ManageWords: string
{
    case START = '/start';
    case HELP = 'help';
    case CLEAR = 'clear';

    public static function tryFromCode(string $message): ?self
    {
        return match ($message) {
            self::START->value => self::START,
            self::HELP->value => self::HELP,
            self::CLEAR->value => self::CLEAR,
            default => null,
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::START => 'Start',
            self::HELP => 'Help',
            self::CLEAR => 'Clear all subscriptions',
        };
    }

    public static function getSelectList(): array
    {
        return [
            self::START->value => self::START->getLabel(),
            self::HELP->value  => self::HELP->getLabel(),
            self::CLEAR->value => self::CLEAR->getLabel(),
        ];
    }
}
