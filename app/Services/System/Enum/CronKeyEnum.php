<?php

declare(strict_types=1);

namespace App\Services\System\Enum;

enum CronKeyEnum: string
{
    case OnlinerCatalogGoods = 'onliner_update_catalog_goods';
    case ClearLogs = 'clear_logs';

    public static function getSelectList(): array
    {
        return [
            self::OnlinerCatalogGoods->value => self::OnlinerCatalogGoods->getLabel(),
            self::ClearLogs->value           => self::ClearLogs->getLabel(),
        ];
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::OnlinerCatalogGoods => 'Обновление каталога товаров Onliner',
            self::ClearLogs => 'Очистка логов',
        };
    }
}
