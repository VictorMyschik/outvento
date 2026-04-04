<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User\Conversations;

use App\Helpers\FileSizeConverter;
use App\Models\Conversations\Conversation;
use App\Models\Conversations\ConversationMessage;
use App\Models\Conversations\ConversationMessageAttachment;
use App\Models\User;
use App\Orchid\Filters\User\ConversationMessageAttachmentsFilter;
use App\Orchid\Filters\User\ConversationMessageFilter;
use App\Orchid\Filters\User\ConversationMessageLinksFilter;
use App\Orchid\Filters\User\ConversationPinnedMessagesFilter;
use App\Orchid\Filters\User\ConversationUsersFilter;
use App\Orchid\Layouts\Lego\AvatarUploadLayout;
use App\Orchid\Layouts\User\Conversations\AddConversationUserEditLayout;
use App\Orchid\Layouts\User\Conversations\ConversationJoinPolicyEditLayout;
use App\Orchid\Layouts\User\Conversations\ConversationMessageAttachmentsLayout;
use App\Orchid\Layouts\User\Conversations\ConversationMessageLinksLayout;
use App\Orchid\Layouts\User\Conversations\ConversationMessageListLayout;
use App\Orchid\Layouts\User\Conversations\ConversationPinnedMessageListLayout;
use App\Orchid\Layouts\User\Conversations\ConversationTypeEditLayout;
use App\Orchid\Layouts\User\Conversations\ConversationUserSettingsEditLayout;
use App\Orchid\Layouts\User\Conversations\ConversationUsersLayout;
use App\Orchid\Layouts\User\Conversations\ConversationVisibilityEditLayout;
use App\Orchid\Layouts\User\Conversations\MessageEditLayout;
use App\Orchid\Layouts\User\Conversations\RenameFileEditLayout;
use App\Orchid\Screens\User\UserBaseScreen;
use App\Services\Conversations\Enum\JoinPolicy;
use App\Services\Conversations\Enum\Role;
use App\Services\Conversations\Enum\Status;
use App\Services\Conversations\Enum\Type;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class UserConversationDetailsScreen extends UserBaseScreen
{
    public ?User $user = null;
    public ?User $secondUser = null;
    public ?Conversation $conversation = null;
    public string $name = 'Users Messages';

    public function name(): string
    {
        return match ($this->conversation->getType()) {
            Type::Private => 'Private conversation',
            default => $this->conversation->getType()->getLabel() . ' conversation',
        };
    }

    public function description(): string
    {
        $link = "<a href='" . route('profiles.details', ['user' => $this->user->id]) . "'>" . $this->user->name . "</a>";

        $description = $link . ' | ' . $this->conversations->getUnreadMessagesCount($this->conversation->id, $this->user->id) . ' unread messages';

        $info = $this->conversations->getConversationUserInfo($this->conversation->id, $this->user->id);
        if ($info->deleted_at) {
            $description = 'Conversation deleted at ' . $info->deleted_at;
        }

        return $description;
    }

    public function commandBar(): iterable
    {
        $backRoute = match ($this->conversation->getType()) {
            Type::Private => 'profiles.conversations.list',
            default => 'profiles.group-conversations.list',
        };

        return [
            Button::make('mark read')
                ->class('mr-btn-primary')
                ->icon('eye')
                ->method('markRead', ['id' => $this->user->id]),
            Link::make('Назад')->class('mr-btn mr-btn-route')->icon('arrow-up')->route($backRoute, ['user' => $this->user->id]),
        ];
    }

    public function query(User $user, Conversation $conversation): array
    {
        $this->setAvatar($conversation->getAvatar());

        if ($conversation->getType() === Type::Private) {
            $this->secondUser = $user;

            foreach ($this->conversations->getConversationUsers($conversation->id()) as $users) {
                if ($users->user_id !== $user->id) {
                    $this->secondUser = $users;
                }
            }
        }

        return [
            'user'             => $user,
            'conversation'     => $conversation,
            'list'             => ConversationMessageFilter::runQuery($conversation->id(), $user->id)->paginate(10),
            'list-attachments' => ConversationMessageAttachmentsFilter::runQuery($conversation->id())->paginate(10, pageName: 'attachments'),
            'list-links'       => ConversationMessageLinksFilter::runQuery($conversation->id())->paginate(10, pageName: 'links'),
            'list-users'       => ConversationUsersFilter::runQuery($conversation->id())->paginate(10, pageName: 'users'),
        ];
    }

    public function layout(): iterable
    {
        $tabs = [
            'Info'            => Layout::rows($this->getInfoLayout()),
            'Pinned messages' => Layout::rows($this->getPinnedMessagesLayout()),
            'Files'           => ConversationMessageAttachmentsLayout::class,
            'Links'           => ConversationMessageLinksLayout::class,
        ];

        if ($this->conversation->getType() !== Type::Private) {
            $tabs['Users'] = Layout::rows($this->getUsersLayout());//ConversationUsersLayout::class;
        }

        return [
            ConversationMessageFilter::displayFilterCard(request(), $this->conversations, $this->conversation->id()),
            Layout::columns([
                ConversationMessageListLayout::class,
                Layout::tabs($tabs),
            ]),
            Layout::rows($this->getSummaryLayout()),
            Layout::rows($this->getActionBottomLinkLayout()),
            Layout::modal('message_edit_modal', MessageEditLayout::class)->async('asyncGetMessage'),
            Layout::modal('rename_file_modal', RenameFileEditLayout::class)->async('asyncGetMessageFile'),
            Layout::modal('conversation_type_modal', ConversationTypeEditLayout::class)->async('asyncGetConversation'),
            Layout::modal('conversation_join_policy_modal', ConversationJoinPolicyEditLayout::class)->async('asyncGetConversation'),
            Layout::modal('conversation_visibility_modal', ConversationVisibilityEditLayout::class)->async('asyncGetConversation'),
            Layout::modal('conversation_users_modal', AddConversationUserEditLayout::class)->async('asyncGetConversation'),
            Layout::modal('conversation_users_edit_modal', ConversationUserSettingsEditLayout::class)->async('asyncGetConversationUser'),
            Layout::modal('upload_conversation_avatar', AvatarUploadLayout::class),
        ];
    }

    private function getPinnedMessagesLayout(): array
    {
        $list = ConversationPinnedMessagesFilter::runQuery($this->conversation->id())->paginate(10, pageName: 'pinned');

        if ($list->count()) {
            $out[] = Group::make([
                Button::make('clear')
                    ->confirm('Are you sure?')
                    ->class('mr-btn-danger')
                    ->method('deleteAllPinnedMessages'),
            ])->autoWidth();
        }

        $table = new ConversationPinnedMessageListLayout()->build(new Repository(['list-pinned' => $list->items()]));

        $out[] = ViewField::make('')->view('admin.row')->value($table);

        return $out;
    }

    public function getUsersLayout(): array
    {
        $out = [];

        $out[] = Group::make([
            ModalToggle::make('add user')
                ->class('mr-btn-success')
                ->method('addConversationUser')
                ->modalTitle('Add user')
                ->modal('conversation_users_modal'),
        ])->autoWidth();

        $list = ConversationUsersFilter::runQuery($this->conversation->id())->paginate(10, pageName: 'users');

        $table = new ConversationUsersLayout()->build(new Repository(['list-users' => $list->items()]));

        $out[] = ViewField::make('')->view('admin.row')->value($table);

        return $out;
    }

    private function getInfoLayout(): array
    {
        $out = $this->avatarTab();

        $out[] = ViewField::make('')->view('hr');

        $rows['header'] = ['Property', 'Value', '#'];

        $rows['body'][] = [
            'Property' => 'Type',
            'Value'    => $this->conversation->getType()->getLabel(),
            '#'        => ModalToggle::make('')->class('fa  fa-pencil mr-btn-primary')->modal('conversation_type_modal')->method('saveConversationType'),
        ];
        $rows['body'][] = [
            'Property' => 'Join Policy',
            'Value'    => $this->conversation->getJoinPolicy()->getLabel(),
            '#'        => ModalToggle::make('')->class('fa  fa-pencil mr-btn-primary')->modal('conversation_join_policy_modal')->method('saveConversationJoinPolicy'),
        ];
        $rows['body'][] = [
            'Property' => 'Visibility',
            'Value'    => $this->conversation->getVisibility()->getLabel(),
            '#'        => ModalToggle::make('')->class('fa  fa-pencil mr-btn-primary')->modal('conversation_visibility_modal')->method('saveConversationVisibility'),
        ];

        $out[] = ViewField::make('')->view('admin.table')->value($rows);
        $out[] = ViewField::make('')->view('hr');
        $out[] = ViewField::make('')->view('admin.created_updated')->value($this->conversation);

        return $out;
    }

    private function avatarTab(): array
    {
        $hasLogo = (bool)$this->conversation->getAvatar();

        $photoTab = [
            Group::make([
                ModalToggle::make('add avatar')
                    ->class('mr-btn-success')
                    ->modal('upload_conversation_avatar')
                    ->modalTitle('Add avatar')
                    ->method('saveConversationAvatar', ['conversationId' => $this->conversation->id]),
                Button::make('delete')
                    ->class('mr-btn-danger')
                    ->method('removeAvatar')
                    ->hidden(!$hasLogo)
                    ->confirm('Delete avatar?')
                    ->parameters(['conversationId' => $this->conversation->id]),
            ])->autoWidth(),
        ];

        $group = ['avatar' => ViewField::make('#')->view('admin.raw')->value('<i>No avatar</i>')];
        if ($this->conversation->avatar) {
            $group['avatar'] = ViewField::make('#')->view('admin.avatar')->value(['path' => $this->conversation->getAvatar()]);
        }

        return array_merge($photoTab, [ViewField::make('')->view('space')], $group);
    }

    public function getActionBottomLinkLayout(): array
    {
        $btn[] = Button::make('Удалить переписку для всех')
            ->class('mr-btn-danger pull-right')
            ->method('purgeUserConversation')
            ->confirm('Вы уверены, что хотите удалить переписку?')
            ->icon('trash');

        if ($this->conversations->getConversationUserInfo($this->conversation->id, $this->user->id)->deleted_at) {
            $btn[] = Button::make('Восстановить переписку для себя')
                ->class('mr-btn-success pull-right')
                ->method('restoreUserConversation')
                ->confirm('Вы уверены, что хотите восстановить переписку?')
                ->icon('refresh');

            return [Group::make($btn)->autoWidth()];
        }

        $btn[] = Button::make('Clear history')
            ->class('mr-btn-danger pull-right')
            ->method('clearHistoryUserConversation')
            ->confirm('Вы уверены, что хотите очистить историю переписки?')
            ->icon('trash');

        return [Group::make($btn)->autoWidth()];
    }

    public function deleteAllPinnedMessages(): void
    {
        $this->conversations->deleteAllPinnedMessages($this->conversation->id);
    }

    public function addToPinned(string $messageId): void
    {
        $this->conversations->addToPinned($this->conversation->id, $messageId, $this->user->id);
    }

    public function removeAvatar(): void
    {
        $this->conversations->removeAvatar($this->conversation);
    }

    public function saveConversationAvatar(Request $request): void
    {
        $file = $request->file('avatar');

        $this->conversations->addAvatar($this->conversation->id, $file);
    }

    public function asyncGetConversationUser(int $userId): array
    {
        return [
            'conversation_user' => (array)$this->conversations->getConversationUserInfo($this->conversation->id, $userId),
        ];
    }

    public function changeUserSettings(Request $request, int $userId): void
    {
        $input = $request->validate([
            'conversation_user.status' => ['required', Rule::enum(Status::class)],
            'conversation_user.role'   => ['required', Rule::in(Role::getSelectList())],
        ])['conversation_user'];

        $this->conversations->updateUserConversation(
            conversationId: $this->conversation->id,
            userId: $userId,
            role: Role::from($input['role']),
            status: Status::from($input['status'])
        );
    }

    public function addConversationUser(Request $request): void
    {
        $input = $request->validate([
            'userIds'   => 'required|array',
            'userIds.*' => 'exists:users,id',
            'status'    => ['required', Rule::enum(Status::class)],
            'role'      => ['required', Rule::enum(Role::class)],
        ]);

        $this->conversations->addUsersToGroupConversation(
            $this->conversation->id,
            $input['userIds'],
            Role::from($input['role']),
            Status::from($input['status'])
        );
    }

    public function asyncGetConversation(): array
    {
        return ['conversation' => $this->conversation];
    }

    public function saveConversationType(Request $request): void
    {
        $type = Type::from($request->input('conversation')['type']);

        $this->conversations->updateConversation($this->conversation->id, ['type' => $type->value]);
    }

    public function saveConversationJoinPolicy(Request $request): void
    {
        $policy = JoinPolicy::from($request->input('conversation')['join_policy']);

        $this->conversations->updateConversation($this->conversation->id, ['join_policy' => $policy->value]);
    }

    public function purgeUserConversation(): RedirectResponse
    {
        $this->conversations->setConversationDeleted($this->conversation->id);

        return redirect()->route('profiles.conversations.list', ['user' => $this->user->id]);
    }

    private function getSummaryLayout(): array
    {
        $attachmentsSizeList = $this->conversations->getConversationAttachmentsSizeByUsers($this->conversation->id());

        $row = [
            ViewField::make('')->view('admin.h6')->value('Files storage usage: '),
        ];

        $table['header'] = ['ID', 'Name', 'Size'];

        $sum = 0;
        foreach ($attachmentsSizeList as $userSize) {
            $sum += (int)$userSize->size;
            $table['body'][] = [
                'ID'   => $userSize->user_id,
                'Name' => "<a href='" . route('profiles.details', ['user' => $userSize->user_id]) . "' target='_blank'>" . $userSize->name . '</a>',
                'Size' => round(FileSizeConverter::bytesTo((int)$userSize->size), 2) . ' MB',

            ];
        }

        $row[] = ViewField::make('')->view('admin.table')->value($table);

        $row[] = Label::make('')->title('Total: ')->value(round(FileSizeConverter::bytesTo($sum), 2) . ' MB')->horizontal();

        return [
            ViewField::make('summary')->view('admin.h5')->value('Summary'),
            ViewField::make('')->view('hr'),
            ...$row,
        ];
    }

    public function clearHistoryUserConversation(): void
    {
        $this->conversations->clearHistoryUserConversation($this->conversation->id, $this->user->id);
    }

    public function restoreUserConversation(): void
    {
        $this->conversations->restoreForUser($this->conversation->id, $this->user->id);
    }

    public function asyncGetMessageFile(int $fileId): array
    {
        return [
            'attachment' => ConversationMessageAttachment::loadByOrDie($fileId),
        ];
    }

    public function saveFileName(Request $request, int $fileId): void
    {
        $this->conversations->checkAccess($this->conversation->id, $this->user->id);

        $name = $request->validate([
            'attachment.name' => 'required|string|max:255',
        ])['attachment']['name'];

        $this->conversations->renameMessageFile($fileId, $name);
    }

    public function markRead(): void
    {
        $this->conversations->checkAccess($this->conversation->id, $this->user->id);

        $lastMessageId = $this->conversations->getLastMessageIdForUser($this->conversation->id, $this->user->id);
        if ($lastMessageId) {
            $this->conversations->setMessageAsRead($this->conversation->id, $this->user->id, $lastMessageId);
        }
    }

    public function asyncGetMessage(?string $messageId = null, ?string $parentId = null): array
    {
        if (is_null($messageId)) {
            return [
                'message' => null,
            ];
        }

        return [
            'message' => ConversationMessage::findOrFail($messageId)->content,
        ];
    }

    public function editMessage(Request $request, string $messageId): void
    {
        $this->conversations->checkAccess($this->conversation->id, $this->user->id);

        $text = $request->validate([
            'message' => 'nullable|string|max:10000',
        ])['message'];

        $files = $request->allFiles()['file'] ?? [];

        if (!empty($files)) {
            $this->conversations->validateAttachments($files);
        }

        $result = $this->conversations->updateMessage($this->conversation->id, $messageId, $this->user->id, $text, $files);

        if (!$result) {
            Toast::error('Error updating message');
        }
    }

    public function saveMessage(Request $request, ?string $parentId = null): void
    {
        $this->conversations->checkAccess($this->conversation->id, $this->user->id);

        $input = $request->validate([
            'message' => 'nullable|string|max:10000',
        ]);

        $files = $request->allFiles()['file'] ?? [];

        if (!empty($files)) {
            $this->conversations->validateAttachments($files);
        }

        if (empty($input['message']) && empty($files)) {
            return;
        }

        $this->conversations->addMessage($this->conversation->id, $this->user->id, $input['message'], $parentId, $files);
    }

    public function removeForMe(string $messageId): void
    {
        $this->conversations->checkAccess($this->conversation->id, $this->user->id);

        $this->conversations->deleteMessageForUser($this->conversation->id, $this->user->id, $messageId);
    }

    public function deleteMessage(string $messageId): void
    {
        $this->conversations->checkAccess($this->conversation->id, $this->user->id);

        $this->conversations->deleteMessageHard($messageId);
    }

    public function deleteAllMessageFiles(string $messageId): void
    {
        $this->conversations->checkAccess($this->conversation->id, $this->user->id);

        $this->conversations->deleteAllMessageFiles($messageId);

        $this->conversations->deleteEmptyMessage($messageId);
    }

    public function deleteMessageFile(string $messageId, int $fileId): void
    {
        $this->conversations->checkAccess($this->conversation->id, $this->user->id);

        $this->conversations->deleteMessageFile($messageId, $fileId);
        $this->conversations->deleteEmptyMessage($messageId);
    }

    public function runFiltering(Request $request): RedirectResponse
    {
        $list = [];
        foreach (ConversationMessageFilter::FIELDS as $item) {
            if (!is_null($request->input($item))) {
                $list[$item] = $request->input($item);
            }
        }

        return redirect()->route('profiles.messages', $list + ['user' => $this->user->id, 'conversation' => $this->conversation->id]);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('profiles.messages', ['user' => $this->user->id, 'conversation' => $this->conversation->id]);
    }
}