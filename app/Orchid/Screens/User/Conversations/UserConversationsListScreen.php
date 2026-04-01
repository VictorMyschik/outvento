<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User\Conversations;

use App\Models\User;
use App\Orchid\Filters\User\ConversationFilter;
use App\Orchid\Layouts\User\Conversations\AddConversationLayout;
use App\Orchid\Layouts\User\Conversations\AddGroupConversationLayout;
use App\Orchid\Layouts\User\Conversations\ConversationListLayout;
use App\Orchid\Layouts\User\Conversations\MessageEditLayout;
use App\Orchid\Screens\User\UserBaseScreen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Support\Facades\Layout;

class UserConversationsListScreen extends UserBaseScreen
{
    public ?User $user = null;
    public string $name = 'Private User Conversations';

    public function description(): string
    {
        return 'ID ' . $this->user->id . (($this->user->getFullName() ? ' | ' . $this->user->getFullName() : '') ?: ' ' . $this->user->name . ' |   ' . $this->user->email);
    }

    public function query(User $user): iterable
    {
        $this->setAvatar($user->getAvatar());

        return [
            'user' => $user,
            'list' => ConversationFilter::runQuery($user->id)->paginate(50),
        ];
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
            Link::make('Назад')->class('mr-btn mr-btn-route')->icon('arrow-up')->route('profiles.details', ['user' => $this->user->id]),
        ];
    }

    public function layout(): iterable
    {
        return [
            ConversationFilter::displayFilterCard(request()),
            ConversationListLayout::class,
            Layout::rows($this->getActionBottomLayout()),

            Layout::modal('add_conversation_modal', AddConversationLayout::class),
            Layout::modal('message_modal', MessageEditLayout::class),
        ];
    }

    private function getActionBottomLayout(): array
    {
        return [
            Group::make([
                Button::make('Purge')
                    ->class('mr-btn-danger pull-right')
                    ->icon('trash')
                    ->method('purgeUserMessages')
                    ->confirm('Are you sure you want to delete all messages for this user? This action cannot be undone.'),
            ])->alignCenter()
        ];
    }

    public function saveMessage(Request $request, int $conversationId, int $userId): void
    {
        $text = $request->validate([
            'message' => 'required|string|max:10000',
        ])['message'];

        $this->conversations->addMessage($conversationId, $userId, $text);
    }

    public function purgeUserMessages(): void
    {
        $this->conversations->removeForUser(null, $this->user->id);
    }

    public function removeForMe(int $conversationId): void
    {
        $this->conversations->removeForUser($conversationId, $this->user->id);
    }

    public function saveConversation(Request $request): RedirectResponse
    {
        $id = $this->conversations->addPersonalConversation(
            ownerId: $this->user->id,
            userId: (int)$request->input('userId'),
        );

        return redirect()->route('profiles.messages', ['user' => $this->user->id, 'conversation' => $id]);
    }

    public function saveGroupConversation(Request $request): RedirectResponse
    {
        $id = $this->conversations->addGroupConversation(
            ownerId: $this->user->id,
            userIds: (array)$request->input('userIds'),
            title: $request->input('title'),
        );

        return redirect()->route('profiles.messages', ['user' => $this->user->id, 'conversation' => $id]);
    }

    public function runFiltering(Request $request): RedirectResponse
    {
        $list = [];
        foreach (ConversationFilter::FIELDS as $item) {
            if (!is_null($request->input($item))) {
                $list[$item] = $request->get($item);
            }
        }

        return redirect()->route('profiles.conversations.list', $list + ['user' => $this->user->id]);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('profiles.conversations.list', ['user' => $this->user->id]);
    }
}