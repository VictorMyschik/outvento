<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Travel;

use App\Models\EmailInvite;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class InviteListLayout extends Table
{
    public $target = 'invite-uih';

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('email', 'User')->render(fn(EmailInvite $invite) => $invite->getEmail()),
            TD::make('status', 'Status')->render(fn(EmailInvite $invite) => $invite->getStatusName()),

            TD::make('updated_at', 'Sent')->sort()->render(fn(EmailInvite $invite) => $invite->getUpdatedObject()?->format('d.m.Y H:i:s')),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(EmailInvite $invite) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Button::make(__('Resend'))
                            ->icon('refresh')
                            ->confirm(__('Resend this invite?'))
                            ->method('resendEmailInvite', ['id' => $invite->id]),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->confirm(__('Are you sure you want to delete the user in travel?'))
                            ->method('removeUIH', ['id' => $invite->id]),
                    ])),
        ];
    }
}
