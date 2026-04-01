<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Conversations;

use App\Models\Conversations\ConversationMessageLink;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ConversationMessageLinksLayout extends Table
{
    public $target = 'list-links';

    public function columns(): array
    {
        return [
            TD::make('id', 'ID')->class('text-nowrap')->sort(),
            TD::make('message_id', 'Message')->render(function (ConversationMessageLink $link) {
                return Link::make('')->icon('filter')->route('profiles.messages', [
                    'user'         => $link->user_id,
                    'conversation' => $link->conversation_id,
                    'messageId'    => $link->message_id,
                ]);
            })->sort(),

            TD::make('url', 'URL')->render(function (ConversationMessageLink $link) {
                return "<a href='{$link->url}' target='_blank'>{$link->url}</a>";
            })->sort(),
            TD::make('created_at', 'Added')->render(function (ConversationMessageLink $link) {
                return Link::make($link->created_at->format('H:i:s d/m/Y'))->route('profiles.messages', [
                    'user'         => $link->user_id,
                    'conversation' => $link->conversation_id,
                    'createdAt'    => $link->created_at->format('Y-m-d'),
                ]);
            })->class('text-nowrap')->alignRight()->sort(),
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
