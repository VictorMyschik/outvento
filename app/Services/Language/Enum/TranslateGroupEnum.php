<?php

declare(strict_types=1);

namespace App\Services\Language\Enum;

use App\Models\Lego\Fields\ActiveFieldTrait;

enum TranslateGroupEnum: int
{
    use ActiveFieldTrait;

    case NAVBAR = 1;
    case FOOTER = 2;
    case HOME = 3;
    case ABOUT = 4;
    case CONTACT = 5;
    case PROFILE = 6;
    case SETTINGS = 7;

    public function getLabel(): string
    {
        return match ($this) {
            self::NAVBAR => 'Navigation Bar',
            self::FOOTER => 'Footer',
            self::HOME => 'Home Page',
            self::ABOUT => 'About Us',
            self::CONTACT => 'Contact Us',
            self::PROFILE => 'User Profile',
            self::SETTINGS => 'Settings',
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