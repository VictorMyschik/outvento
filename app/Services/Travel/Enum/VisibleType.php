<?php

declare(strict_types=1);

namespace App\Services\Travel\Enum;

enum VisibleType: int
{
    case VISIBLE_TYPE_PUBLIC = 2; // публичный
    case VISIBLE_TYPE_FOR_ME = 0; // только для меня, в публичном поиске не участвует
    case VISIBLE_TYPE_PLATFORM = 1; // только для зарегистрированных пользователей

    public static function getSelectList(): array
    {
        return [
            self::VISIBLE_TYPE_PUBLIC->value   => self::VISIBLE_TYPE_PUBLIC->getLabel(),
            self::VISIBLE_TYPE_FOR_ME->value   => self::VISIBLE_TYPE_FOR_ME->getLabel(),
            self::VISIBLE_TYPE_PLATFORM->value => self::VISIBLE_TYPE_PLATFORM->getLabel(),
        ];
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::VISIBLE_TYPE_PUBLIC => __('mr-t.Public'),
            self::VISIBLE_TYPE_FOR_ME => __('mr-t.only_for_me'),
            self::VISIBLE_TYPE_PLATFORM => __('mr-t.Only for registered users'),
        };
    }

    public function getDescription(): array
    {
        return match ($this) {
            self::VISIBLE_TYPE_PUBLIC => __('mr-t.Anyone can see this travel program'), // 'Любой пользователь может видеть эту походную программу',
            self::VISIBLE_TYPE_FOR_ME => __('mr-t.Only I can see this travel program'), // 'Только я могу видеть эту походную программу',
            self::VISIBLE_TYPE_PLATFORM => __('mr-t.Only registered users can see this travel program'), // 'Только зарегистрированные пользователи могут видеть эту походную программу',
        };
    }

}
