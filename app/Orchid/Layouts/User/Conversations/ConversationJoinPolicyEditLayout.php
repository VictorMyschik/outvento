<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Conversations;

use App\Services\Conversations\Enum\JoinPolicy;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class ConversationJoinPolicyEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Select::make('conversation.join_policy')
                ->required()
                ->title('Join Policy')
                ->options(JoinPolicy::getSelectList()),
        ];
    }
}