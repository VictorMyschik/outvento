<?php

declare(strict_types=1);

namespace App\Services\Language\Enum;

use App\Models\Lego\Fields\ActiveFieldTrait;

enum TranslateGroupEnum: int
{
    case Common = 1;
    // Pages
    case PageWelcome = 4;
    case PageHome = 5;
    case PageAbout = 6;
    case PageContact = 7;
    case PageProfile = 8;

    public function getLabel(): string
    {
        return match ($this) {
            self::Common => 'Common',
            self::PageHome => 'Home Page',
            self::PageAbout => 'About Us',
            self::PageContact => 'Contact Us',
            self::PageProfile => 'User Profile',
            self::PageWelcome => 'Welcome Page',
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