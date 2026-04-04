<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Conversations;

use App\Services\Conversations\Enum\JoinPolicy;
use App\Services\Conversations\Enum\Visibility;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class ConversationVisibilityEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Select::make('conversation.visibility')
                ->required()
                ->title('Visibility')
                ->options(Visibility::getSelectList()),
        ];
    }
}