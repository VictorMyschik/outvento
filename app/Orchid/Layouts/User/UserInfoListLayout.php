<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use App\Models\User;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class UserInfoListLayout extends Table
{
    public $target = 'list';

    public function columns(): array
    {
        return [
            TD::make('id', 'ID')->sort(),
            TD::make('name', 'Login')->sort(),
            TD::make('email', 'Email')->sort(),
            TD::make('email_verified_at', 'Email Verified At')->render(fn(User $user) => $user->email_verified_at)->active()->sort(),
            TD::make('telegram_chat_id', 'Telegram Chat ID')->sort(),
            TD::make('first_name', 'First Name')->sort(),
            TD::make('last_name', 'Last Name')->sort(),
            TD::make('language', 'Language')->render(fn(User $user) => $user->getLanguage()->getLabel())->sort(),
            TD::make('gender', 'Gender')->render(fn(User $user) => $user->getGender()?->getLabel())->sort(),
            TD::make('birthday', 'Birthday')->render(fn(User $user) => $user->birthday?->format('d.m.Y'))->sort(),
            TD::make('about', 'About')->sort(),
            TD::make('permissions', 'Permissions')->render(fn(User $user) => (bool)$user->permissions)->active()->sort(),

            TD::make('created_at', 'Created')
                ->render(fn(User $user) => $user->created_at->format('d.m.Y H:i:s'))
                ->sort()
                ->defaultHidden(),
            TD::make('updated_at', 'Updated')
                ->render(fn(User $user) => $user->updated_at?->format('d.m.Y H:i:s'))
                ->sort()
                ->defaultHidden(),

            TD::make('#')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn($user) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('Edit')
                            ->icon('pencil')
                            ->modal('user_modal')
                            ->modalTitle('User id ' . $user->id)
                            ->method('saveUser')
                            ->asyncParameters(['id' => $user->id]),
                    ])),
        ];
    }
}
