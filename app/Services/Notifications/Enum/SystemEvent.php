<?php

declare(strict_types=1);

namespace App\Services\Notifications\Enum;

enum SystemEvent: string
{
    case NewNewsSubscription = 'new_news_subscription';
    case VerifyCommunicationEmail = 'verify_communication_email';
    case RegistrationConfirmation = 'registration_confirmation';
    case TravelInvite = 'travel_invite';

    public function getLabel(): string
    {
        return match ($this) {
            self::NewNewsSubscription => 'New News Subscription',
            self::VerifyCommunicationEmail => 'Verify Communication Email',
            self::RegistrationConfirmation => 'Registration Confirmation',
            self::TravelInvite => 'Travel Invite',
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
