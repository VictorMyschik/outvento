<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\System;

use App\Models\NotificationToken;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class NotificationTokenListLayout extends Table
{
    protected $target = 'list';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')->sort(),
            TD::make('type', 'Type')->render(fn(NotificationToken $notificationToken) => $notificationToken->getType()->getLabel())->sort(),
            TD::make('channel', 'Channel')->render(fn(NotificationToken $notificationToken) => $notificationToken->getChannel()->getLabel())->sort(),
            TD::make('address', 'Address')->sort(),
            TD::make('token', 'Token')->sort(),
            TD::make('', 'Body')->render(function (NotificationToken $notificationToken) {
                return ModalToggle::make('')
                    ->icon('eye')
                    ->modal('details_modal')
                    ->modalTitle($notificationToken->getType()->getLabel())
                    ->asyncParameters(['id' => $notificationToken->id()]);
            })->sort(),
            TD::make('created_at', 'Created')
                ->render(fn(NotificationToken $notificationToken) => $notificationToken->created_at->format('d.m.Y H:i:s'))
                ->sort(),
            TD::make('updated_at', 'Updated')
                ->render(fn(NotificationToken $notificationToken) => $notificationToken->updated_at?->format('d.m.Y H:i:s'))
                ->sort(),
            TD::make('#', 'Действия')->render(function (NotificationToken $notificationToken) {
                return DropDown::make()->icon('options-vertical')->list([
                    Button::make('resend')
                        ->confirm('Resend notification for this token?')
                        ->icon('refresh')
                        ->method('resendNotificationToken')
                        ->parameters(['id' => $notificationToken->id()]),
                    Button::make('Delete')
                        ->confirm('Delete this notification token?')
                        ->icon('trash')
                        ->method('deleteNotificationToken')
                        ->parameters(['id' => $notificationToken->id()]),
                ]);
            }),
        ];
    }

    public function hoverable(): bool
    {
        return true;
    }
}