<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use App\Models\User;
use App\Models\UserInfo\Communication;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class UserCommunicateListLayout extends Table
{
    public $target = 'list';

    public function columns(): array
    {
        return [
            TD::make('id', 'ID')->sort(),
            TD::make('name', 'Login')->sort(),
            TD::make('email', 'Email')->sort(),
            TD::make('telegram_chat_id', 'Telegram Chat ID')->sort(),
            TD::make('full_name', 'Full Name')->sort(),

            TD::make('created_at', 'Created')
                ->render(fn(Communication $communicate) => $communicate?->created_at?->format('d.m.Y H:i:s'))
                ->sort()
                ->defaultHidden(),
            TD::make('updated_at', 'Updated')
                ->render(fn(Communication $communicate) => $communicate?->updated_at?->format('d.m.Y H:i:s'))
                ->sort()
                ->defaultHidden(),

            TD::make('#')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn($communicate) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                    /*    Link::make(__('Profile'))
                            ->icon('user')
                            ->route('users.details', $communicate?->id),
                        ModalToggle::make('Edit')
                            ->icon('pencil')
                            ->modal('user_modal')
                            ->modalTitle('User id ' . $communicate?->id)
                            ->method('saveUser')
                            ->asyncParameters(['id' => $communicate?->id]),*/
                    ])),
        ];
    }
}
