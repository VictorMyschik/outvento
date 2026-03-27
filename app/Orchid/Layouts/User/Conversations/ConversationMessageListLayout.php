<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Conversations;

use App\Models\Conversations\ConversationMessage;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
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
                $message->avatar = route('api.v1.user.avatar', ['user' => $message->user_id]);
                $items = [
                    'Created: ' . $message->created_at->format('H:i:s d/m/Y'),
                ];

                if ($message->edited_at) {
                    $items[] = 'Edited: ' . $message->edited_at?->format('H:i:s d/m/Y');
                }

                if ($message->deleted_at) {
                    $items[] = 'Deleted: ' . $message->deleted_at?->format('H:i:s d/m/Y');
                }

                $items = array_merge($items, [
                    ModalToggle::make('')
                        ->icon('pencil')
                        ->modal('message_edit_modal')
                        ->modalTitle('Edit Message')
                        ->method('editMessage')
                        ->asyncParameters(['messageId' => $message->id]),
                    Button::make('')
                        ->icon('bs.trash3')
                        ->confirm('Are you sure you want to delete this message?')
                        ->method('deleteMessage', ['messageId' => $message->id]),
                    Button::make('for me')
                        ->icon('bs.trash3')
                        ->confirm('Are you sure you want to delete this message for yourself?')
                        ->method('removeForMe', ['messageId' => $message->id]),
                ]);

                $message->btns = Group::make($items)->autoWidth();

                return ViewField::make('')->view('admin.users.conversations.message')->value($message);
            })->sort(),
        ];
    }

    public function hoverable(): false
    {
        return false;
    }
}
