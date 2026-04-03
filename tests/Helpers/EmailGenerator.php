<?php

declare(strict_types=1);

namespace Tests\Helpers;

final readonly class EmailGenerator
{
    private const array EMAIL_DOMAINS = [
        'gmail.com',
        'yahoo.com',
        'hotmail.com',
        'outlook.com',
        'mail.ru',
        'yandex.ru',
        'rambler.ru',
        'tut.by',
    ];

    public static function generateEmail(string $name): string
    {
        return strtolower($name) . rand(1, 100000) . '@' . array_rand(array_flip(self::EMAIL_DOMAINS));
    }
}