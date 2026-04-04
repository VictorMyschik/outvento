<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Conversations;

use App\Models\User;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Layouts\Rows;

class AddPersonalConversationLayout extends Rows
{
    public function fields(): array
    {
        return [
            Relation::make('userId')
                ->required()
                ->fromModel(User::class, 'name', 'id')
                ->value(request()->get('userId'))
                ->title('User'),
        ];
    }
}