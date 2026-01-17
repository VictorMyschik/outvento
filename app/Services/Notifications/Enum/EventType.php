<?php

declare(strict_types=1);

namespace App\Services\Notifications\Enum;

enum EventType: string
{
    case Invite = 'invite';
    case Feedback = 'feedback';
    case News = 'news';
    case NewNewsSubscription = 'new_news_subscription';

    public function getLabel(): string
    {
        return match ($this) {
            self::Invite => 'Invite',
            self::Feedback => 'Feedback',
            self::News => 'News',
            self::NewNewsSubscription => 'New News Subscription',
        };
    }

    public static function getSelectList(): array
    {
        return array_combine(
            array_map(fn($enum) => $enum->value, self::cases()),
            array_map(fn($enum) => $enum->getLabel(), self::cases())
        );
    }

    public static function getSelectListForGuest(): array
    {
        return [
            self::News->value => self::News->getLabel(),
        ];
    }

    public static function getAllowedForUser(): array
    {
        return [
            self::Invite,
            self::News,
        ];
    }
}
