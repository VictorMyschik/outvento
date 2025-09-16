<?php

declare(strict_types=1);

namespace App\Services\Catalog\Wildberries\Enum;

// https://openapi.wildberries.ru/statistics/api/ru/#tag/Statistika/paths/~1api~1v1~1supplier~1orders/get
enum WBOrderTypeEnum: int
{
    case CLIENT = 1;
    case RETURN_DEFECT = 2;
    case RETURN_FORCED = 3;
    case RETURN_ANONYMOUS = 4;
    case RETURN_WRONG_ATTACHMENT = 5;
    case RETURN_SELLER = 6;
    case RETURN_FROM_REVIEW = 7;
    case AUTO_RETURN_MP = 8;
    case RETURN_INCOMPLETE_SET = 9;
    case RETURN_KGT = 10;

    public static function fromString(string $str): WBOrderTypeEnum
    {
        return match ($str) {
            'Клиентский' => self::CLIENT,
            'Возврат Брака' => self::RETURN_DEFECT,
            'Принудительный возврат' => self::RETURN_FORCED,
            'Возврат обезлички' => self::RETURN_ANONYMOUS,
            'Возврат Неверного Вложения' => self::RETURN_WRONG_ATTACHMENT,
            'Возврат Продавца' => self::RETURN_SELLER,
            'Возврат из Отзыва' => self::RETURN_FROM_REVIEW,
            'АвтоВозврат МП' => self::AUTO_RETURN_MP,
            'Недокомплект (Вина продавца)' => self::RETURN_INCOMPLETE_SET,
            'Возврат КГТ' => self::RETURN_KGT,
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::CLIENT => 'Клиентский',
            self::RETURN_DEFECT => 'Возврат Брака',
            self::RETURN_FORCED => 'Принудительный возврат',
            self::RETURN_ANONYMOUS => 'Возврат обезлички',
            self::RETURN_WRONG_ATTACHMENT => 'Возврат Неверного Вложения',
            self::RETURN_SELLER => 'Возврат Продавца',
            self::RETURN_FROM_REVIEW => 'Возврат из Отзыва',
            self::AUTO_RETURN_MP => 'АвтоВозврат МП',
            self::RETURN_INCOMPLETE_SET => 'Недокомплект (Вина продавца)',
            self::RETURN_KGT => 'Возврат КГТ',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::CLIENT => 'Заказ, поступивший от покупателя',
            self::RETURN_DEFECT => 'Возврат товара продавцу по причине брака',
            self::RETURN_FORCED => 'Принудительный возврат товара продавцу',
            self::RETURN_ANONYMOUS => 'Возврат товара продавцу обезлички',
            self::RETURN_WRONG_ATTACHMENT => 'Возврат товара продавцу по причине неверного вложения',
            self::RETURN_SELLER => 'Возврат товара продавцу',
            self::RETURN_FROM_REVIEW => 'Возврат товара продавцу из отзыва',
            self::AUTO_RETURN_MP => 'АвтоВозврат товара продавцу',
            self::RETURN_INCOMPLETE_SET => 'Возврат товара продавцу по причине недокомплекта (вина продавца)',
            self::RETURN_KGT => 'Возврат товара продавцу по причине КГТ',
        };
    }

    public static function getSelectList(): array
    {
        return [
            self::CLIENT->value                  => self::CLIENT->getLabel(),
            self::RETURN_DEFECT->value           => self::RETURN_DEFECT->getLabel(),
            self::RETURN_FORCED->value           => self::RETURN_FORCED->getLabel(),
            self::RETURN_ANONYMOUS->value        => self::RETURN_ANONYMOUS->getLabel(),
            self::RETURN_WRONG_ATTACHMENT->value => self::RETURN_WRONG_ATTACHMENT->getLabel(),
            self::RETURN_SELLER->value           => self::RETURN_SELLER->getLabel(),
            self::RETURN_FROM_REVIEW->value      => self::RETURN_FROM_REVIEW->getLabel(),
            self::AUTO_RETURN_MP->value          => self::AUTO_RETURN_MP->getLabel(),
            self::RETURN_INCOMPLETE_SET->value   => self::RETURN_INCOMPLETE_SET->getLabel(),
            self::RETURN_KGT->value              => self::RETURN_KGT->getLabel(),
        ];
    }
}
