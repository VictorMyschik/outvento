<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Conversations;

use App\Models\User;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ConversationUsersLayout extends Table
{
    public $target = 'list-users';

    public function columns(): array
    {
        return [
            TD::make('id', 'ID')->class('text-nowrap')->sort(),
            TD::make('name', 'name')->class('text-nowrap')->sort(),
            TD::make('email', 'Email')->class('text-nowrap')->sort(),
            TD::make('status', 'Status')->class('text-nowrap')->sort(),
            TD::make('role', 'Role')->class('text-nowrap')->sort(),
            TD::make('#', '#')->render(function (User $user) {
                return DropDown::make()->icon('options-vertical')->list([
                    ModalToggle::make('edit')
                        ->icon('pencil')
                        ->modal('conversation_users_edit_modal')
                        ->method('changeUserSettings')
                        ->parameters(['userId' => $user->id]),
                ]);
            }),
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
