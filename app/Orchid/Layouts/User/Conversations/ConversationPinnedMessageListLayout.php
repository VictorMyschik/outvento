<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Conversations;

use App\Models\Conversations\ConversationPinnedMessage;
use Carbon\Carbon;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ConversationPinnedMessageListLayout extends Table
{
    public $target = 'list-pinned';

    public function columns(): array
    {
        return [
            TD::make('user_name', 'User')->sort(),
            TD::make('content', 'Content'),
            TD::make('pinned_at', 'Pinned at')->render(fn(ConversationPinnedMessage $message) => new Carbon($message->pinned_at)->format('H:i:s d/m/Y'))->sort(),
            TD::make('created_at', 'Created at')->sort(),
            TD::make('#', '#')->render(function (ConversationPinnedMessage $message) {
                return DropDown::make()->icon('options-vertical')->list([
                    Button::make('delete')->icon('trash')
                        ->method('deletePinnedMessage')
                        ->parameters(['messageId' => $message->id]),
                ]);
            }),
        ];
    }
}
