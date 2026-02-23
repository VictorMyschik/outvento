<?php

declare(strict_types=1);

namespace App\Services\Travel\Enum;

enum UITStatus: int
{
    case New = 0; // Новый участник
    case Confirmed = 1; // Подтверждённый всеми сторонами
    case Rejected = 2; // Отклонённый всеми сторонами

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'Новый участник',
            self::Confirmed => 'Подтверждённый всеми сторонами',
            self::Rejected => 'Отклонённый всеми сторонами',
        };
    }

    public static function getSelectList(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $case) => [
                $case->value => $case->getLabel(),
            ])
            ->toArray();
    }
}
