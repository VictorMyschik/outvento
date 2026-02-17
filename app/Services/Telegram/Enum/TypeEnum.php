<?php

declare(strict_types=1);

namespace App\Services\Telegram\Enum;

enum TypeEnum: int
{
    case Notification = 1;
    case Alert = 2;
    case Reminder = 3;
    case AddBotConnection = 4;

    public function getLabel(): string
    {
        return match ($this) {
            TypeEnum::Notification => 'Уведомление',
            TypeEnum::Alert => 'Оповещение',
            TypeEnum::Reminder => 'Напоминание',
            TypeEnum::AddBotConnection => 'Подключение бота к контакту',
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
