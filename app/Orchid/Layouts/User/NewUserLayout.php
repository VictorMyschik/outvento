<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use App\Services\System\Enum\Language;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class NewUserLayout extends Rows
{
    public function fields(): array
    {
        return [
            Group::make([
                Input::make('name')->type('text')->max(255)->required()->title('Name (login)'),
                Input::make('email')->type('email')->required()->title('Email'),
            ]),

            Input::make('password')->type('password')->required()->title('Password'),
            Input::make('password_confirmation')->type('password')->required()->title('Password Confirmation'),

            Select::make('language')
                ->title('Language')
                ->options(Language::getSelectList()),
        ];
    }
}
