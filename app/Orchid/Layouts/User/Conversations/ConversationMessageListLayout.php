<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Conversations;

use App\Helpers\FileSizeConverter;
use App\Helpers\Linkify;
use App\Models\Conversations\ConversationMessage;
use App\Services\Conversations\ConversationRepositoryInterface;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ConversationMessageListLayout extends Table
{
    public $target = 'list';

    public function __construct(private readonly ConversationRepositoryInterface $repository) {}

    public function columns(): array
    {
        return [
            TD::make('content', 'Content')->render(function (ConversationMessage $message) {
                $message->avatar = route('api.v1.user.avatar', ['user' => $message->user_id]);

                $message->created = $message->created_at->format('H:i:s d/m/Y');
                $message->edited = $message->edited_at?->format('H:i:s d/m/Y');

                $message->btns = DropDown::make()->icon('options-vertical')->list([
                    ViewField::make('')->view('admin.raw')->value(Group::make([
                        ModalToggle::make('edit')
                            ->icon('pencil')
                            ->modal('message_edit_modal')
                            ->modalTitle('Edit Message')
                            ->method('editMessage')
                            ->asyncParameters(['messageId' => $message->id]),
                        Button::make('for all')
                            ->icon('bs.trash3')
                            ->confirm('Are you sure you want to delete this message?')
                            ->method('deleteMessage', ['messageId' => $message->id]),
                        Button::make('for me')
                            ->icon('bs.trash3')
                            ->confirm('Are you sure you want to delete this message for yourself?')
                            ->method('removeForMe', ['messageId' => $message->id]),
                    ])->autoWidth())
                ]);

                $message->files = $this->repository->getMessageFiles($message->id);

                if (!empty(trim((string)$message->content))) {
                    $message->content = Linkify::linkify($message->content);
                }

                $files = [];
                foreach ($message->files as $file) {
                    $files[] = $file->size;
                }

                $message->sumFileSize = round(FileSizeConverter::bytesTo(array_sum($files)), 2) . ' Mb';
                $message->downloadAllFiles = Link::make('')->route('api.v1.admin.conversation.attachment.get.zip', [
                    'conversationId' => $message->conversation_id,
                    'messageId'      => $message->id,
                ])->icon('download');
                $message->btnAllFileDelete = Button::make('')->icon('trash')
                    ->confirm('Are you sure you want to delete all files from this message?')
                    ->method('deleteAllMessageFiles', ['messageId' => $message->id]);

                return ViewField::make('')->view('admin.users.conversations.message')->value($message);
            })->sort(),
        ];
    }

    protected function textNotFound(): string
    {
        return $this->buildNewMessageForm()->toHtml();
    }

    protected function iconNotFound(): string
    {
        return 'icon-table';
    }

    protected function subNotFound(): string
    {
        return 'Add the first message to start the conversation. You can add some files. ';
    }

    public function total(): array
    {
        return [
            TD::make('content', 'Total')->render(function () {
                return $this->buildNewMessageForm();
            })
        ];
    }

    public function buildNewMessageForm(): ViewField
    {
        $object = new \stdClass();

        $object->textarea = TextArea::make('message')
            ->rows(5)
            ->maxlength(10000);
        $object->viewField = ViewField::make('')->view('space');
        $object->attachments = Input::make('attachments')->type('file')->name('file')->multiple();
        $object->location = Input::make('attachments')->type('file')->name('file')->multiple();
        $object->btn = Button::make('save')
            ->class('mr-btn-success pull-right')
            ->method('saveMessage');

        return ViewField::make('')->view('admin.users.conversations.add_message')->value($object);
    }

    public function hoverable(): false
    {
        return false;
    }

    public function bordered(): false
    {
        return false;
    }
}
