<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User;

use App\Models\Conversations\Conversation;
use App\Models\User;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;

class UserMessagesScreen extends UserBaseScreen
{
    public ?User $user = null;
    public string $name = 'Users Messages';

    public function description(): string
    {
        return 'ID ' . $this->user->id . (($this->user->getFullName() ? ' | ' . $this->user->getFullName() : '') ?: ' ' . $this->user->name . ' |   ' . $this->user->email);
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('New')
                ->icon('plus')
                ->class('mr-btn-success pull-left')
                ->modal('add_conversation_modal')
                ->modalTitle('Add User Conversation')
                ->method('saveConversation'),
            Link::make('Назад')->class('mr-btn mr-btn-route')->icon('arrow-up')->route('profiles.messages.list', ['user' => $this->user->id]),
        ];
    }

    public function query(User $user, ?Conversation $conversation = null): iterable
    {
        $this->setAvatar($user->getAvatar());
        return [
            'user' => $user,
        ];
    }

    public function layout(): iterable
    {
        return [];
    }
}