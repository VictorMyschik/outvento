<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Conversations;

use App\Services\Conversations\Enum\Role;
use App\Services\Conversations\Enum\Status;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class ConversationUserSettingsEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Select::make('conversation_user.role')
                ->required()
                ->options(Role::getSelectList())
                ->title('Role'),

            Select::make('conversation_user.status')
                ->required()
                ->options(Status::getSelectList())
                ->title('Status'),
        ];
    }
}
