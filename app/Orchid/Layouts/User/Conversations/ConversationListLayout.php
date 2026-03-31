<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Conversations;

use App\Models\Conversations\ConversationUser;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ConversationListLayout extends Table
{
    public $target = 'list';

    public function columns(): array
    {
        return [
            TD::make('conversation_id', 'Conversations ID')->render(function (ConversationUser $conversationUser) {
                return Link::make(substr((string)$conversationUser->conversation_id, 0, 50))
                    ->stretched()
                    ->route('profiles.messages', ['user' => $this->query->get('user'), 'conversation' => $conversationUser->conversation_id]);
            })->sort(),
            TD::make('user_id', 'User ID')->sort(),
            TD::make('name', 'User Name')->sort(),
            TD::make('email', 'User Email')->sort(),
            TD::make('full_name', 'User Full Name')->sort(),
            TD::make('content', 'Last Message')->render(function (ConversationUser $conversationUser) {
                return Link::make(substr((string)$conversationUser->content, 0, 50))->route('profiles.messages', ['user' => $this->query->get('user'), 'conversation' => $conversationUser->conversation_id]);
            }),
            TD::make('created_at', 'Date')->render(function (ConversationUser $conversationUser) {
                return $conversationUser->created_at?->format('H:i:s d/m/Y');
            })->sort(),
        ];
    }

    public function hoverable(): true
    {
        return true;
    }
}
