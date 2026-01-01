<?php

declare(strict_types=1);

namespace App\Services\Language\Enum;

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
            self::PageWelcome => 'Welcome Page',
            self::PageHome => 'Home Page',
            self::PageAbout => 'About Us',
            self::PageContact => 'Contact Us',
            self::PageProfile => 'User Profile',
        };
    }

    public static function getSelectList(): array
    {
        return array_combine(
            array_map(fn($enum) => $enum->value, self::cases()),
            array_map(fn($enum) => $enum->getLabel(), self::cases())
        );
    }

    public static function fromLabel(string $label): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->getLabel() === $label) {
                return $case;
            }
        }

        return null;
    }
}