<?php

declare(strict_types=1);

namespace App\Services\Language\Enum;

enum TranslateGroupEnum: int
{
    case Common = 1;
    case Email = 2;
    case Auth = 3;
    case Emails = 4;
    case Enums = 5;
    case Pagination = 6;
    case Passwords = 7;
    case Register = 8;
    case Validation = 9;
    // Pages
    case PageWelcome = 10;
    case PageAbout = 11;
    case PageProfile = 12;

    public static function fromCode(string $code): self
    {
        return match ($code) {
            'common' => self::Common,
            'email' => self::Email,
            'auth' => self::Auth,
            'emails' => self::Emails,
            'enums' => self::Enums,
            'pagination' => self::Pagination,
            'passwords' => self::Passwords,
            'register' => self::Register,
            'validation' => self::Validation,
            'page_welcome' => self::PageWelcome,
            'page_about' => self::PageAbout,
            'page_profile' => self::PageProfile,
            default => throw new \InvalidArgumentException("Invalid code: $code"),
        };
    }

    public function getCode(): string
    {
        return match ($this) {
            self::Common => 'common',
            self::Email => 'email',
            self::Auth => 'auth',
            self::Emails => 'emails',
            self::Enums => 'enums',
            self::Pagination => 'pagination',
            self::Passwords => 'passwords',
            self::Register => 'register',
            self::Validation => 'validation',
            self::PageWelcome => 'page_welcome',
            self::PageAbout => 'page_about',
            self::PageProfile => 'page_profile',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Common => 'Common',
            self::Email => 'Email',
            self::PageWelcome => 'Welcome Page',
            self::PageAbout => 'About Us',
            self::PageProfile => 'User Profile',
            self::Auth => 'Authentication',
            self::Emails => 'Emails',
            self::Enums => 'Enums',
            self::Pagination => 'Pagination',
            self::Passwords => 'Passwords',
            self::Register => 'Registration',
            self::Validation => 'Validation',
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