<?php

declare(strict_types=1);

namespace App\Services\Travel\Enum;

enum UITStatus: int
{
    case NEW = 0; // Новый участник
    case APPROVED = 1; // Подтверждённый всеми сторонами
    case REJECTED = 2; // Отклонённый всеми сторонами

    public function getLabel(): string
    {
        return match ($this) {
            self::NEW => 'Новый участник',
            self::APPROVED => 'Подтверждённый всеми сторонами',
            self::REJECTED => 'Отклонённый всеми сторонами',
        };
    }

    public static function getSelectList(): array
    {
        return [
            self::NEW->value      => self::NEW->getLabel(),
            self::APPROVED->value => self::APPROVED->getLabel(),
            self::REJECTED->value => self::REJECTED->getLabel(),
        ];
    }
}
