<?php

declare(strict_types=1);

namespace App\Services\Forms\Enum;

enum FormTypeEnum: int
{
    case Feedback = 1;
    case NewNewsSubscription = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::Feedback => 'Обратная связь',
            self::NewNewsSubscription => 'Новая подписка на новости',
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
