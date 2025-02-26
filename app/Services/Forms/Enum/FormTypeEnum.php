<?php

declare(strict_types=1);

namespace App\Services\Forms\Enum;

enum FormTypeEnum: int
{
    case FEEDBACK = 1;

    public function getLabel(): string
    {
        return match ($this) {
            self::FEEDBACK => 'Обратная связь',
        };
    }

    public static function getSelectList(): array
    {
        return [
            self::FEEDBACK->value => self::FEEDBACK->getLabel(),
        ];
    }
}
