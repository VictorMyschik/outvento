<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Conversations;

use App\Helpers\FileSizeConverter;
use App\Models\Conversations\ConversationMessageAttachment;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ConversationMessageAttachmentsLayout extends Table
{
    public $target = 'list-attachments';

    public function columns(): array
    {
        return [
            TD::make('id', 'ID')->class('text-nowrap')->sort(),
            TD::make('user_name', 'User')->class('text-nowrap')->sort(),
            TD::make('message_id', 'Message')->render(function (ConversationMessageAttachment $attachment) {
                return Link::make('')->icon('filter')->route('profiles.messages', [
                    'user'         => $attachment->user_id,
                    'conversation' => $attachment->conversation_id,
                    'messageId'    => $attachment->message_id,
                ]);
            })->sort(),

            TD::make('name', 'Name')->render(function (ConversationMessageAttachment $attachment) {
                return "<a href='{$attachment->path}'>{$attachment->name}</a>";
            })->sort(),
            TD::make('size', 'Size')->class('text-nowrap')->render(function (ConversationMessageAttachment $attachment) {
                return round(FileSizeConverter::bytesTo((int)$attachment->size), 2) . ' Mb';
            })->alignRight()->sort(),
            TD::make('created_at', 'Added')->class('text-nowrap')->render(function ($attachment) {
                return Link::make($attachment->created_at->format('H:i:s d/m/Y'))->route('profiles.messages', [
                    'user'         => $attachment->user_id,
                    'conversation' => $attachment->conversation_id,
                    'createdAt'    => $attachment->created_at->format('Y-m-d'),
                ]);
            })->alignRight()->sort(),
            TD::make('#')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(ConversationMessageAttachment $attachment) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('rename')
                            ->modal('rename_file_modal')
                            ->method('saveFileName')
                            ->modalTitle('Rename File')
                            ->asyncParameters(['fileId' => $attachment->id])
                            ->icon('pencil'),
                        Button::make('delete')
                            ->icon('bs.trash3')
                            ->confirm(__('Are you sure you want to delete the faq?'))
                            ->method('deleteMessageFile', ['messageId' => $attachment->message_id, 'fileId' => $attachment->id]),
                    ])),
        ];
    }

    public function total(): array
    {
        return [
            TD::make('total')
                ->alignRight()
                ->colspan(4)
                ->render(fn() => 'Total for this page: ' . round(FileSizeConverter::bytesTo((int)$this->query->get($this->target)->sum('size')), 2) . ' Mb'),
            TD::make('', ''),
            TD::make('', ''),
        ];
    }

    public function hoverable(): true
    {
        return true;
    }

    protected function onEachSide(): int
    {
        return 10;
    }
}
