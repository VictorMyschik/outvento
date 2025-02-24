<?php

declare(strict_types=1);

namespace App\Services\Travel\Enum;

enum TravelStatus: int
{
    case STATUS_DRAFT = -1;
    case STATUS_ACTIVE = 1;
    case STATUS_ARCHIVED = 2;

    public static function getSelectList(): array
    {
        return [
            self::STATUS_DRAFT->value    => self::STATUS_DRAFT->getLabel(),
            self::STATUS_ACTIVE->value   => self::STATUS_ACTIVE->getLabel(),
            self::STATUS_ARCHIVED->value => self::STATUS_ARCHIVED->getLabel(),
        ];
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::STATUS_DRAFT => __('mr-t.Draft'),
            self::STATUS_ACTIVE => __('mr-t.Active'),
            self::STATUS_ARCHIVED => __('mr-t.Archived'),
        };
    }
}
