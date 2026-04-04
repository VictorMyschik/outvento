<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User\Conversations;

use App\Models\User;
use App\Orchid\Filters\User\GroupConversationFilter;
use App\Orchid\Layouts\User\Conversations\AddGroupConversationLayout;
use App\Orchid\Layouts\User\Conversations\AddPersonalConversationLayout;
use App\Orchid\Layouts\User\Conversations\GroupConversationListLayout;
use App\Orchid\Layouts\User\Conversations\MessageEditLayout;
use App\Orchid\Screens\User\UserBaseScreen;
use App\Services\Conversations\DTO\GroupConversationDto;
use App\Services\Conversations\Enum\JoinPolicy;
use App\Services\Conversations\Enum\Status;
use App\Services\Conversations\Enum\Type;
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
                ->modalTitle('Add Group User Conversation')
                ->method('saveGroupConversation'),
            Link::make('Назад')->class('mr-btn mr-btn-route')->icon('arrow-up')->route('profiles.details', ['user' => $this->user->id]),
        ];
    }

    public function layout(): iterable
    {
        return [
            GroupConversationFilter::displayFilterCard(request()),
            GroupConversationListLayout::class,

            Layout::modal('add_conversation_modal', AddPersonalConversationLayout::class),
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
        $userIds = (array)$request->input('userIds');

        foreach ($userIds as $key => $userId) {
            if ($userId === $this->user->id) {
                unset($userIds[$key]);
            }
        }

        $id = $this->conversations->addGroupConversation(
            new GroupConversationDto(
                ownerId: $this->user->id,
                userIds: (array)$request->input('userIds'),
                title: $request->input('title'),
                type: Type::from((string)$request->input('type')),
                joinPolicy: JoinPolicy::from($request->input('joinPolicy')),
                status: Status::Active,
            ),
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