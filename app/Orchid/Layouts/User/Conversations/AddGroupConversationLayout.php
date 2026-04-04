<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Conversations;

use App\Models\User;
use App\Services\Conversations\Enum\JoinPolicy;
use App\Services\Conversations\Enum\Type;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class AddGroupConversationLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('title')->title('Title')->required(),
            Relation::make('userIds')
                ->multiple()
                ->required()
                ->fromModel(User::class, 'name', 'id')
                ->title('Users'),
            Select::make('type')
                ->title('Type')
                ->options(Type::getSelectGroupList()),
            Select::make('joinPolicy')
                ->title('Join Policy')
                ->options(JoinPolicy::getSelectList()),
        ];
    }
}
