<?php

declare(strict_types=1);

namespace App\Services\Travel\Enum;

enum UITStatus: int
{
    case NEW = 0; // Новый участник
    case CONFIRMED = 1; // Подтверждённый всеми сторонами
    case REJECTED = 2; // Отклонённый всеми сторонами

    public function getLabel(): string
    {
        return match ($this) {
            self::NEW => 'Новый участник',
            self::CONFIRMED => 'Подтверждённый всеми сторонами',
            self::REJECTED => 'Отклонённый всеми сторонами',
        };
    }

    public static function getSelectList(): array
    {
        return [
            self::NEW->value       => self::NEW->getLabel(),
            self::CONFIRMED->value => self::CONFIRMED->getLabel(),
            self::REJECTED->value  => self::REJECTED->getLabel(),
        ];
    }
}
