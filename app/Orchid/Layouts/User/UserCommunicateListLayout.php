<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use App\Models\UserInfo\Communication;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
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
            TD::make('full_name', 'Full Name')->sort(),
            TD::make('type', 'Type')->sort(),
            TD::make('address', 'Address')->sort(),
            TD::make('description', 'Description')->sort(),

            TD::make('created_at', 'Created')
                ->render(fn(Communication $communicate) => $communicate->created_at->format('d.m.Y H:i:s'))
                ->sort()
                ->defaultHidden(),
            TD::make('updated_at', 'Updated')
                ->render(fn(Communication $communicate) => $communicate->updated_at?->format('d.m.Y H:i:s'))
                ->sort()
                ->defaultHidden(),

            TD::make('#')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn($communicate) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('изменить')
                            ->modal('communicate_modal')
                            ->method('saveCommunication')
                            ->modalTitle('Изменить контакт')
                            ->asyncParameters(['id' => $communicate->id()])
                            ->icon('pencil'),
                        Button::make('удалить')
                            ->method('removeCommunication')
                            ->confirm('Вы уверены, что хотите удалить этот контакт?')
                            ->parameters(['userId' => $communicate->user_id,'id' => $communicate->id()])
                            ->icon('trash'),
                    ])),
        ];
    }
}
