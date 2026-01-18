<?php

declare(strict_types=1);

namespace App\Services\Telegram;

final class TelegramMarkdown
{
    public static function escape(string $text): string
    {
        return preg_replace(
            '/([_*\[\]()~`>#+\-=|{}.!])/u',
            '\\\\$1',
            $text
        );
    }
}

