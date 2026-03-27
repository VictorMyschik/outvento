<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Conversations;

use App\Models\User;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Layouts\Rows;

class AddGroupConversationLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('title')->title('Title')->required(),

            Relation::make('userIds')
                ->multiple()
                ->fromModel(User::class, 'name', 'id')
                ->title('Users'),
        ];
    }
}