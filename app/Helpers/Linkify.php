<?php

declare(strict_types=1);

namespace App\Helpers;

class Linkify
{
    public static function linkify(string $text): string
    {
        $text = e($text);

        // заменяем URL на ссылки
        return preg_replace(
            '/(https?:\/\/[^\s]+)/i',
            '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>',
            $text
        );
    }
}