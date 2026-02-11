<?php

declare(strict_types=1);

namespace App\Services\User\Enum;

enum Visibility: int
{
    case Private = 0;
    case Public = 1;
    case FriendsOnly = 2;
    case RegisteredUsers = 3;

    public function getLabel(): string
    {
        return __('enums.users_visibility.' . $this->name);
    }

    public static function getSelectList(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [
                $case->value => $case->getLabel(),
            ])
            ->toArray();
    }
}