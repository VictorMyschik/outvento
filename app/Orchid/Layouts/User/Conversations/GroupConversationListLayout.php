<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Conversations;

use App\Models\Conversations\Conversation;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class GroupConversationListLayout extends Table
{
    public $target = 'list';

    public function columns(): array
    {
        return [
            TD::make('conversation_id', 'Conversations ID')->render(function (Conversation $conversation) {
                return Link::make((string)$conversation->id)
                    ->stretched()
                    ->route('profiles.group-messages', ['user' => $this->query->get('user'), 'conversation' => $conversation->id]);
            }),
            TD::make('title', 'Title')->sort(),
            TD::make('created_at', 'Date')->render(function (Conversation $conversation) {
                return $conversation->created_at?->format('H:i:s d/m/Y');
            })->sort(),
        ];
    }

    public function hoverable(): true
    {
        return true;
    }
}
