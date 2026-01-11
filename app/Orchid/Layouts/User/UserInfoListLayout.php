<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class UserInfoListLayout extends Table
{
    public $target = 'list';

    public function columns(): array
    {
        // 'users.id',
        // 'name',
        // 'email',
        // 'language',
        // 'users.created_at',
        // 'email_verified_at',
        // 'birthday',
        // 'gender',
        // 'full_name',
        // 'about',
        return [
            TD::make('id', 'ID')->sort(),
            TD::make('name', 'Login')->sort(),
            TD::make('email', 'User')->render(fn($user) => $user->email)->sort(),
            TD::make('full_name', 'Full Name')->sort(),


            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn($user) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('Edit')
                            ->icon('pencil')
                            ->modal('translate')
                            ->modalTitle('Translate id ' . $user->id)
                            ->method('saveTranslate')
                            ->asyncParameters(['id' => $user->id]),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->confirm(__('Are you sure you want to delete the Translate?'))
                            ->method('remove', ['id' => $user->id]),
                    ])),
        ];
    }
}