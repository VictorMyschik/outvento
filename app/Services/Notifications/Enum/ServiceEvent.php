<?php

declare(strict_types=1);

namespace App\Services\Notifications\Enum;

enum ServiceEvent: int
{
    case Feedback = 1;
    case Invite = 2;
    case Comment = 3;
    case Other = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::Invite => 'Invite',
            self::Feedback => 'Feedback',
            self::Comment => 'Comment',
            self::Other => 'Other',
        };
    }

    public static function getSelectList(): array
    {
        return array_combine(
            array_map(fn($enum) => $enum->value, self::cases()),
            array_map(fn($enum) => $enum->getLabel(), self::cases())
        );
    }

    public function audiences(): array
    {
        return match ($this) {
            self::Invite,
            self::Comment => [NotificationAudience::User],

            self::Feedback => [
                NotificationAudience::Support,
                NotificationAudience::Admin,
            ],

            self::Other => [NotificationAudience::Admin],
        };
    }

    public static function selectListForAudiences(array $audiences): array
    {
        $audiences = array_map(
            fn(NotificationAudience $a) => $a->value,
            $audiences
        );

        $out = [];

        foreach (self::cases() as $case) {
            $caseAudiences = array_map(
                fn(NotificationAudience $a) => $a->value,
                $case->audiences()
            );

            if (array_intersect($audiences, $caseAudiences)) {
                $out[$case->value] = $case->getLabel();
            }
        }

        return $out;
    }
}
