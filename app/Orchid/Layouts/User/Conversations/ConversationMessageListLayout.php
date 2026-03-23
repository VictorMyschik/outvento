<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Conversations;

use App\Models\Conversations\ConversationMessage;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ConversationMessageListLayout extends Table
{
    public $target = 'list';

    public function columns(): array
    {
        return [
            TD::make('content', 'Content')->render(function (ConversationMessage $message) {
                return ViewField::make('')->view('admin.users.conversations.message')->value($message);
            })->sort(),
            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(ConversationMessage $message) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('edit')
                            ->icon('pencil')
                            ->modal('message_edit_modal')
                            ->modalTitle('Edit Message')
                            ->method('editMessage')
                            ->asyncParameters(['messageId' => $message->id]),
                        Button::make('delete')
                            ->icon('bs.trash3')
                            ->confirm('Are you sure you want to delete this message?')
                            ->method('deleteMessage', ['messageId' => $message->id]),
                        Button::make('delete for me')
                            ->icon('bs.trash3')
                            ->confirm('Are you sure you want to delete this message for yourself?')
                            ->method('removeForMe', ['messageId' => $message->id]),
                    ])),
        ];
    }

    public function hoverable(): true
    {
        return true;
    }
}
