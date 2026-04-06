<?php

declare(strict_types=1);

namespace App\Services\Albums\Enum;

enum Visibility: string
{
    case Private = 'private';
    case Public = 'public';
    case RegisteredUsers = 'registered_users';

    public function getLabel(): string
    {
        return __('enums.users_visibility.' . $this->name);
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
