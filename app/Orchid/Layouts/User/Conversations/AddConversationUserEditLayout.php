<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Conversations;

use App\Models\User;
use App\Services\Conversations\Enum\Role;
use App\Services\Conversations\Enum\Status;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class AddConversationUserEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Relation::make('userIds')
                ->required()
                ->multiple()
                ->fromModel(User::class, 'name', 'id')
                ->title('Users'),

            Select::make('role')
                ->required()
                ->options(Role::getSelectList())
                ->title('Role'),

            Select::make('status')
                ->required()
                ->options(Status::getSelectList())
                ->title('Status'),
        ];
    }
}