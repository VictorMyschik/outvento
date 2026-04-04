<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Conversations;

use App\Services\Conversations\Enum\Type;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class ConversationTypeEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Select::make('conversation.type')
                ->required()
                ->title('Type')
                ->options(Type::getSelectGroupList()),
        ];
    }
}