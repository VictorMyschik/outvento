<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User\Conversations;

use App\Models\Conversations\Conversation;
use App\Models\Conversations\ConversationMessage;
use App\Models\User;
use App\Orchid\Filters\User\ConversationMessageFilter;
use App\Orchid\Layouts\User\Conversations\ConversationMessageListLayout;
use App\Orchid\Layouts\User\Conversations\MessageEditLayout;
use App\Orchid\Screens\User\UserBaseScreen;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Support\Facades\Layout;

class UserGroupConversationDetailsScreen extends UserBaseScreen
{
    public ?User $user = null;
    public ?Conversation $conversation = null;
    public string $name = 'Users Messages';

    public function name(): string
    {
        return $this->user->name . ' messages';
    }

    public function description(): string
    {
        $link = "<a href='" . route('profiles.details', ['user' => $this->user->id]) . "'>" . $this->user->name . "</a>";
        return $link . ' | ' . $this->conversations->getUnreadMessagesCount($this->conversation->id, $this->user->id) . ' unread messages';
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('add message')
                ->class('mr-btn-success')
                ->icon('plus')
                ->modal('message_modal')
                ->modalTitle('Add Message')
                ->method('saveMessage')
                ->asyncParameters(['conversationId' => $this->conversation->id, 'userId' => $this->user->id]),
            Button::make('mark read')
                ->class('mr-btn-primary')
                ->icon('eye')
                ->method('markRead', ['id' => $this->user->id]),
            Link::make('Назад')->class('mr-btn mr-btn-route')->icon('arrow-up')->route('profiles.group-conversations.list', ['user' => $this->user->id]),
        ];
    }

    public function markRead(): void
    {
        $lastMessageId = $this->conversations->getLastMessageIdForUser($this->conversation->id, $this->user->id);
        $this->conversations->setMessageAsRead($this->conversation->id, $this->user->id, $lastMessageId);
    }

    public function query(User $user, ?Conversation $conversation = null): iterable
    {
        $this->setAvatar($user->getAvatar());

        return [
            'user'         => $user,
            'conversation' => $conversation,
            'list'         => ConversationMessageFilter::runQuery($conversation->id(), $user->id)->paginate(50),
        ];
    }

    public function editMessage(Request $request, string $messageId): void
    {
        $text = $request->validate([
            'message' => 'required|string|max:10000',
        ])['message'];

        $this->conversations->updateMessage($messageId, $text);
    }

    public function layout(): iterable
    {
        return [
            ConversationMessageListLayout::class,
            Layout::modal('message_modal', MessageEditLayout::class),
            Layout::modal('message_edit_modal', MessageEditLayout::class)->async('asyncGetMessage'),
        ];
    }

    public function asyncGetMessage(string $messageId): array
    {
        return [
            'message' => ConversationMessage::findOrFail($messageId)->content,
        ];
    }

    public function saveMessage(Request $request, int $conversationId, int $userId): void
    {
        $text = $request->validate([
            'message' => 'required|string|max:10000',
        ])['message'];

        $this->conversations->addMessage($conversationId, $userId, $text);
    }

    public function removeForMe(string $messageId): void
    {
        $this->conversations->deleteMessageForUser($this->conversation->id, $this->user->id, $messageId);
    }

    public function deleteMessage(string $messageId): void
    {
        $this->conversations->deleteMessage($messageId);
    }
}