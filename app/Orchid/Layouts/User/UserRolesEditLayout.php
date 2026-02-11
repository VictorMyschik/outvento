<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

final class UserRolesEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Select::make('roles')
                ->options($this->query->get('roleOptions', []))
                ->multiple()
                ->value($this->query->get('roles', []))
                ->title('Roles'),
        ];
    }
}