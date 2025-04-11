<?php

declare(strict_types=1);

namespace App\Services\Travel\Enum;

enum ImageType: int
{
    case LOGO = 1;
    case PHOTO = 2;

    public static function getSelectList(): array
    {
        return [
            self::LOGO->value  => self::LOGO->getLabel(),
            self::PHOTO->value => self::PHOTO->getLabel(),
        ];
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::LOGO => 'Логотип',
            self::PHOTO => 'Фото',
        };
    }
}
