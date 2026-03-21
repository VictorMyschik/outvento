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
            TD::make('conversation_id', 'Conversations ID')->sort(),
            TD::make('user_id', 'User ID')->render(function (ConversationUser $conversationUser) {
                return Link::make((string)$conversationUser->user_id)->target('_blank')->route('profiles.details', ['user' => $conversationUser->user_id]);
            })->sort(),
            TD::make('name', 'User Name')->sort(),
            TD::make('email', 'User Email')->sort(),
            TD::make('full_name', 'User Full Name')->sort(),
            TD::make('content', 'Last Message')->render(function (ConversationUser $conversationUser) {
                return Link::make(substr((string)$conversationUser->content, 0, 50));
            }),
            TD::make('created_at', 'Date')->render(function (ConversationUser $conversationUser) {
                return $conversationUser->created_at?->format('H:i:s d/m/Y');
            })->sort(),
            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(ConversationUser $conversationUser) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('add message')
                            ->icon('plus')
                            ->modal('message_modal')
                            ->modalTitle('Add Message')
                            ->method('saveMessage')
                            ->asyncParameters(['conversationId' => $conversationUser->conversation_id, 'userId' => $conversationUser->user_id]),

                        Button::make('purge conversation')
                            ->icon('bs.trash3')
                            ->confirm('Are you sure you want to purge this conversation?')
                            ->method('purgeConversation', ['conversationId' => $conversationUser->conversation_id]),

                        Button::make('remove for me')
                            ->icon('bs.trash3')
                            ->confirm('Are you sure you want to purge this conversation?')
                            ->method('removeForMe', ['conversationId' => $conversationUser->conversation_id]),
                    ])),
        ];
    }

    public function hoverable(): true
    {
        return true;
    }
}
