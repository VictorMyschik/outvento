<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User\Conversations;

use App\Models\User;
use App\Orchid\Filters\User\GroupConversationFilter;
use App\Orchid\Layouts\User\Conversations\AddConversationLayout;
use App\Orchid\Layouts\User\Conversations\AddGroupConversationLayout;
use App\Orchid\Layouts\User\Conversations\GroupConversationListLayout;
use App\Orchid\Layouts\User\Conversations\MessageEditLayout;
use App\Orchid\Screens\User\UserBaseScreen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Support\Facades\Layout;

class UserGroupConversationsListScreen extends UserBaseScreen
{
    public ?User $user = null;
    public string $name = 'Users Group Conversations';

    public function description(): string
    {
        return 'ID ' . $this->user->id . (($this->user->getFullName() ? ' | ' . $this->user->getFullName() : '') ?: ' ' . $this->user->name . ' |   ' . $this->user->email);
    }

    public function query(User $user): iterable
    {
        $this->setAvatar($user->getAvatar());

        return [
            'user' => $user,
            'list' => GroupConversationFilter::runQuery($user->id)->paginate(50),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('New group')
                ->icon('plus')
                ->class('mr-btn-success pull-left')
                ->modal('add_group_conversation_modal')
                ->modalTitle('Add User Conversation')
                ->method('saveGroupConversation'),
            Link::make('Назад')->class('mr-btn mr-btn-route')->icon('arrow-up')->route('profiles.details', ['user' => $this->user->id]),
        ];
    }

    public function layout(): iterable
    {
        return [
            GroupConversationFilter::displayFilterCard(request()),
            GroupConversationListLayout::class,

            Layout::modal('add_conversation_modal', AddConversationLayout::class),
            Layout::modal('add_group_conversation_modal', AddGroupConversationLayout::class),
            Layout::modal('message_modal', MessageEditLayout::class),
        ];
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
        foreach (GroupConversationFilter::FIELDS as $item) {
            if (!is_null($request->input($item))) {
                $list[$item] = $request->input($item);
            }
        }

        return redirect()->route('profiles.group-conversations.list', $list + ['user' => $this->user->id]);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('profiles.group-conversations.list', ['user' => $this->user->id]);
    }
}