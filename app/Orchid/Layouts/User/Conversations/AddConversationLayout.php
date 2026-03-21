<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Conversations;

use App\Models\User;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Layouts\Rows;

class AddConversationLayout extends Rows
{
    public function fields(): array
    {
        return [
            Relation::make('user_id')
                ->fromModel(User::class, 'name', 'id')
                ->value(request()->get('user_id'))
                ->title('User'),
        ];
    }
}