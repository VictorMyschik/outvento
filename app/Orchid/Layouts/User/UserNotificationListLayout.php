<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use App\Models\User;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class UserNotificationListLayout extends Table
{
    public $target = 'list';

    public function columns(): array
    {
        return [
            TD::make('id', 'ID')->render(fn(User $user) => Link::make((string)$user->id)->route('profiles.details', $user->id)->stretched())->sort(),
            TD::make('message', 'Message')->sort(),

            TD::make('created_at', 'Created')
                ->render(fn(User $user) => $user->created_at->format('d.m.Y H:i:s'))
                ->sort(),
            TD::make('read_at', 'Read')
                ->render(fn(User $user) => $user->read_at?->format('d.m.Y H:i:s'))
                ->sort(),
        ];
    }

    public function hoverable(): true
    {
        return true;
    }
}
