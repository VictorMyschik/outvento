<?php

declare(strict_types=1);

namespace App\Services\Notifications\Enum;

enum EventType: string
{
    case Feedback = 'feedback';
    case Invite = 'invite';
    case News = 'news';
    case NewNewsSubscription = 'new_news_subscription';
    case VerifyCommunicationEmail = 'verify_communication_email';

    public function getLabel(): string
    {
        return match ($this) {
            self::Invite => 'Invite',
            self::Feedback => 'Feedback',
            self::News => 'News',
            self::NewNewsSubscription => 'New News Subscription',
            self::VerifyCommunicationEmail => 'Verify Communication Email',
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
