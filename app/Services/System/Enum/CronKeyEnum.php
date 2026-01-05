<?php

declare(strict_types=1);

namespace App\Services\System\Enum;

enum CronKeyEnum: string
{
    case OnlinerCatalogGoods = 'onliner_update_catalog_goods';
    case ClearLogs = 'clear_logs';
    case NewsletterDispatch = 'newsletter_dispatch';

    public static function getSelectList(): array
    {
        return array_combine(
            array_map(fn($enum) => $enum->value, self::cases()),
            array_map(fn($enum) => $enum->getLabel(), self::cases())
        );
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::OnlinerCatalogGoods => 'Обновление каталога товаров Onliner',
            self::ClearLogs => 'Очистка логов',
            self::NewsletterDispatch => 'Рассылка новостей',
        };
    }
}
