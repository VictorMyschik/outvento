<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\System;

use App\Models\NotificationCode;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class NotificationCodeListLayout extends Table
{
    protected $target = 'list';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')->sort(),
            TD::make('channel', 'Channel')->render(fn(NotificationCode $notificationCode) => $notificationCode->getChannel()->getLabel())->sort(),
            TD::make('user_id', 'User')->render(fn(NotificationCode $notificationCode) => $notificationCode->getUser()->email)->sort(),
            TD::make('address', 'Address')->sort(),
            TD::make('code', 'Code')->sort(),
            TD::make('type', 'Type')->render(fn(NotificationCode $notificationCode) => $notificationCode->getType()->getLabel())->sort(),
            TD::make('created_at', 'Created')
                ->render(fn(NotificationCode $notificationToken) => $notificationToken->created_at->format('d.m.Y H:i:s'))
                ->sort(),
            TD::make('updated_at', 'Updated')
                ->render(fn(NotificationCode $notificationToken) => $notificationToken->updated_at?->format('d.m.Y H:i:s'))
                ->sort(),
            TD::make('#', 'Действия')->render(function (NotificationCode $notificationCode) {
                return DropDown::make()->icon('options-vertical')->list([
                    Button::make('resend')
                        ->confirm('Resend notification for this code?')
                        ->icon('refresh')
                        ->method('resendNotificationCode')
                        ->parameters(['id' => $notificationCode->id()]),
                    Button::make('Delete')
                        ->confirm('Delete this notification token?')
                        ->icon('trash')
                        ->method('deleteNotificationCode')
                        ->parameters(['id' => $notificationCode->id()]),
                ]);
            }),
        ];
    }

    public function hoverable(): bool
    {
        return true;
    }
}