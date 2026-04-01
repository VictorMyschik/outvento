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
use App\Orchid\Layouts\User\Conversations\ConversationMessageAttachmentsLayout;
use App\Orchid\Layouts\User\Conversations\ConversationMessageLinksLayout;
use App\Orchid\Layouts\User\Conversations\ConversationMessageListLayout;
use App\Orchid\Layouts\User\Conversations\MessageEditLayout;
use App\Orchid\Layouts\User\Conversations\RenameFileEditLayout;
use App\Orchid\Screens\User\UserBaseScreen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\ViewField;
use Orchid\Support\Facades\Layout;

class UserConversationDetailsScreen extends UserBaseScreen
{
    public ?User $user = null;
    public ?User $secondUser = null;
    public ?Conversation $conversation = null;
    public string $name = 'Users Messages';

    public function name(): string
    {
        return $this->user->name . ' messages with ' . $this->secondUser->name . ' conversations';
    }

    public function description(): string
    {
        $link = "<a href='" . route('profiles.details', ['user' => $this->user->id]) . "'>" . $this->user->name . "</a>";

        $description = $link . ' | ' . $this->conversations->getUnreadMessagesCount($this->conversation->id, $this->user->id) . ' unread messages';

        $info = $this->conversations->getConversationUserInfo($this->conversation, $this->user);
        if ($info->deleted_at) {
            $description = 'Conversation deleted at ' . $info->deleted_at;
        }

        return $description;
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('mark read')
                ->class('mr-btn-primary')
                ->icon('eye')
                ->method('markRead', ['id' => $this->user->id]),
            Link::make('Назад')->class('mr-btn mr-btn-route')->icon('arrow-up')->route('profiles.conversations.list', ['user' => $this->user->id]),
        ];
    }

    public function query(User $user, ?Conversation $conversation = null): iterable
    {
        $this->setAvatar($user->getAvatar());
        $this->secondUser = $user;

        foreach ($this->conversations->getConversationUsers($conversation->id()) as $users) {
            if ($users->user_id !== $user->id) {
                $this->secondUser = $users;
            }
        }

        return [
            'user'             => $user,
            'conversation'     => $conversation,
            'list'             => ConversationMessageFilter::runQuery($conversation->id(), $user->id)->paginate(10),
            'list-attachments' => ConversationMessageAttachmentsFilter::runQuery($conversation->id())->paginate(10, pageName: 'attachments'),
            'list-links'       => ConversationMessageLinksFilter::runQuery($conversation->id())->paginate(10, pageName: 'links'),
        ];
    }

    public function layout(): iterable
    {
        return [
            ConversationMessageFilter::displayFilterCard(request(), $this->conversations, $this->conversation->id()),
            Layout::columns([
                ConversationMessageListLayout::class,
                Layout::tabs([
                    'Files' => ConversationMessageAttachmentsLayout::class,
                    'Links' => ConversationMessageLinksLayout::class,
                ]),
            ]),
            Layout::rows($this->getSummaryLayout()),
            Layout::rows($this->getActionBottomLinkLayout()),
            Layout::modal('message_edit_modal', MessageEditLayout::class)->async('asyncGetMessage'),
            Layout::modal('rename_file_modal', RenameFileEditLayout::class)->async('asyncGetMessageFile'),
        ];
    }

    public function getActionBottomLinkLayout(): array
    {
        $btn[] = Button::make('Удалить переписку для всех')
            ->class('mr-btn-danger pull-right')
            ->method('purgeUserConversation')
            ->confirm('Вы уверены, что хотите удалить переписку?')
            ->icon('trash');

        if ($this->conversations->getConversationUserInfo($this->conversation, $this->user)->deleted_at) {
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
        $name = $request->validate([
            'attachment.name' => 'required|string|max:255',
        ])['attachment']['name'];

        $this->conversations->renameMessageFile($fileId, $name);
    }

    public function markRead(): void
    {
        $lastMessageId = $this->conversations->getLastMessageIdForUser($this->conversation->id, $this->user->id);
        if ($lastMessageId) {
            $this->conversations->setMessageAsRead($this->conversation->id, $this->user->id, $lastMessageId);
        }
    }

    public function editMessage(Request $request, string $messageId): void
    {
        $text = $request->validate([
            'message' => 'required|string|max:10000',
        ])['message'];

        $files = $request->allFiles()['file'] ?? [];

        if (!empty($files)) {
            $this->conversations->validateAttachments($files);
        }

        $this->conversations->updateMessage($this->conversation->id, $messageId, $this->user->id, $text, $files);
    }

    public function asyncGetMessage(string $messageId): array
    {
        return [
            'message' => ConversationMessage::findOrFail($messageId)->content,
        ];
    }

    public function saveMessage(Request $request): void
    {
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

        $this->conversations->addMessage($this->conversation->id, $this->user->id, $input['message'], $files);
    }

    public function removeForMe(string $messageId): void
    {
        $this->conversations->deleteMessageForUser($this->conversation->id, $this->user->id, $messageId);
    }

    public function deleteMessage(string $messageId): void
    {
        $this->conversations->deleteMessage($messageId);
    }

    public function deleteAllMessageFiles(string $messageId): void
    {
        $this->conversations->deleteAllMessageFiles($messageId);

        $this->conversations->deleteEmptyMessage($messageId);
    }

    public function deleteMessageFile(string $messageId, int $fileId): void
    {
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