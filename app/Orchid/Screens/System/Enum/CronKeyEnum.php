<?php

declare(strict_types=1);

namespace App\Orchid\Screens\System\Enum;

enum CronKeyEnum: string
{
    case UPDATE_SHOP_GOODS = 'update_shop_goods';
    case UPDATE_CATALOG = 'update_catalog';
    case LOAD_GOOD_METRICS = 'load_good_metrics';

    public function getLabel(): string
    {
        return match ($this) {
            self::UPDATE_SHOP_GOODS => 'Обновление товаров магазинов',
            self::UPDATE_CATALOG => 'Обновление каталога (справочники)',
            self::LOAD_GOOD_METRICS => 'Загрузка метрик товаров',
        };
    }

    public static function getSelectList(): array
    {
        return [
            self::UPDATE_SHOP_GOODS->value => self::UPDATE_SHOP_GOODS->getLabel(),
            self::UPDATE_CATALOG->value    => self::UPDATE_CATALOG->getLabel(),
            self::LOAD_GOOD_METRICS->value => self::LOAD_GOOD_METRICS->getLabel(),
        ];
    }
}
