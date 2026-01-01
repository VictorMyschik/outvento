<?php

declare(strict_types=1);

namespace App\Services\Language\Enum;

enum TranslateGroupEnum: int
{
    case Common = 1;
    case Email = 2;
    // Pages
    case PageWelcome = 10;
    case PageAbout = 11;
    case PageProfile = 12;

    public function getLabel(): string
    {
        return match ($this) {
            self::Common => 'Common',
            self::Email => 'Email',
            self::PageWelcome => 'Welcome Page',
            self::PageAbout => 'About Us',
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