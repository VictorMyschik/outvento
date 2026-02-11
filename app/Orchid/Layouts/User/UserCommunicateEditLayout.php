<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use App\Models\User;
use App\Models\UserInfo\CommunicationType;
use App\Services\User\Enum\Visibility;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class UserCommunicateEditLayout extends Rows
{
    public function fields(): array
    {
        if (!$this->query->get('user_id')) {
            $out = [
                Relation::make('user_id')
                    ->fromModel(User::class, 'email', 'id')
                    ->value(request()->get('user_id'))
                    ->title('User'),
            ];
        }

        $out[] = Select::make('visibility')
            ->options(Visibility::getSelectList())
            ->value(request()->get('visibility'))
            ->title('Visibility');

        $out[] = Relation::make('type_id')
            ->fromModel(CommunicationType::class, 'name_ru', 'id')
            ->value(request()->get('type'))
            ->title('Type');

        $out[] = Input::make('address')
            ->value(request()->get('address'))
            ->title('Address');

        $out[] = Input::make('description')
            ->value(request()->get('description'))
            ->title('Description');

        return $out;
    }
}
