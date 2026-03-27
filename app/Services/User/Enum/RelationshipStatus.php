<?php

declare(strict_types=1);

namespace App\Services\User\Enum;

enum RelationshipStatus: int
{
    case NotSpecified = 0;
    case Single = 1;
    case InRelationship = 2;
    case Married = 3;
    case Looking = 4;

    public function getLabel(): string
    {
        return __('enums.relationship_status.' . $this->name);
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
