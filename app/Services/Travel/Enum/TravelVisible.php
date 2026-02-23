<?php

declare(strict_types=1);

namespace App\Services\Travel\Enum;

enum TravelVisible: int
{
    case ForMe = 0; // только для меня, в публичном поиске не участвует
    case Platform = 1; // только для зарегистрированных пользователей
    case Public = 2; // публичный
    case ByLink = 3; // по ссылке, в публичном поиске не участвует

    public static function getSelectList(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $case) => [
                $case->value => $case->getLabel(),
            ])
            ->toArray();
    }

    public function getLabel(): string
    {
        return __('enums.travel_visible.' . $this->name);
    }

    public function getDescription(): array
    {
        return __('enums.travel_visible_description.' . $this->name);
    }
}
