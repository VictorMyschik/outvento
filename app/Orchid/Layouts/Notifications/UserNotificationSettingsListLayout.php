<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Notifications;

use App\Models\Notification\ServiceNotification;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class UserNotificationSettingsListLayout extends Table
{
    protected $target = 'list';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')->sort(),
            TD::make('event', 'Тип')->render(fn(ServiceNotification $setting) => $setting->getEventType()->getLabel())->sort(),
            TD::make('channel', 'Канал')->render(fn(ServiceNotification $setting) => $setting->getChannel()->getLabel())->sort(),
            TD::make('communication_address', 'Address')->sort(),
            TD::make('user_id', 'User ID')->sort(),
            TD::make('user_id', 'User')->render(fn(ServiceNotification $setting) => $setting->getUser()?->name ?? '—')->sort(),
            TD::make('email', 'User email')->sort(),
            TD::make('created_at', 'Дата создания')->render(fn($setting) => $setting->created_at->format('d/m/Y H:i:s'))->sort(),

            TD::make('#', 'Действия')->render(function (ServiceNotification $setting) {
                return DropDown::make()->icon('options-vertical')->list([
                    ModalToggle::make('изменить')
                        ->icon('pencil')
                        ->modal('user_notification_modal')
                        ->modalTitle('Update User Setting')
                        ->method('saveUserSetting')
                        ->asyncParameters(['id' => $setting->id()]),
                    Button::make('удалить')
                        ->confirm('Delete row?')
                        ->icon('trash')
                        ->method('deleteRow')
                        ->parameters(['id' => $setting->id()]),
                ]);
            }),
        ];
    }

    protected function hoverable(): bool
    {
        return true;
    }
}
