<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use App\Models\User;
use App\Models\UserInfo\CommunicationType;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Layouts\Rows;

class UserCommunicateEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Relation::make('user_id')
                ->fromModel(User::class, 'email', 'id')
                ->value(request()->get('email'))
                ->title('Email (registered)'),

            Relation::make('type')
                ->fromModel(CommunicationType::class, 'name_ru')
                ->value(request()->get('type'))
                ->title('Type'),

            Input::make('address')
                ->value(request()->get('address'))
                ->title('Address'),

            Input::make('description')
                ->value(request()->get('description'))
                ->title('Description'),
        ];
    }
}
