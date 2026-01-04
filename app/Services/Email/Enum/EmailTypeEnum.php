<?php

declare(strict_types=1);

namespace App\Services\Email\Enum;

enum EmailTypeEnum: int
{
    case Invite = 1;
    case Feedback = 2;
    case News = 3;
    case NewNewsSubscription = 4;

    public function getLabel(): string
    {
        return match ($this) {
            EmailTypeEnum::Invite => 'Заявка в Travel',
            EmailTypeEnum::Feedback => 'Обратная связь',
            EmailTypeEnum::News => 'Новости',
            EmailTypeEnum::NewNewsSubscription => 'Новая подписка на новости',
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
