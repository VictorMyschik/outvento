<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Notifications;

use App\Models\MessageLog\TelegramLog;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class TelegramLogListLayout extends Table
{
    protected $target = 'list';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')->sort(),
            TD::make('type', 'Тип')->render(function (TelegramLog $log) {
                return $log->getType()->getLabel();
            })->sort(),
            TD::make('', 'Просмотреть')->render(function (TelegramLog $log) {
                return ModalToggle::make('тело письма')
                    ->icon('eye')
                    ->modalTitle($log->message)
                    ->modal('view_body')
                    ->parameters(['id' => $log->id()]);
            }),
            TD::make('user_tg', 'User TG')->sort(),
            TD::make('created_at', 'Дата')->render(fn($group) => $group->created_at->format('d/m/Y H:i:s'))->sort(),

            TD::make('#', 'Действия')->render(function (TelegramLog $log) {
                return DropDown::make()->icon('options-vertical')->list([
                    Button::make('Resend')
                        ->confirm('Resend message?')
                        ->icon('reload')
                        ->method('resendTelegramMessage')
                        ->parameters(['id' => $log->id()]),
                    Button::make('Delete')
                        ->confirm('Delete row?')
                        ->icon('trash')
                        ->method('deleteRow')
                        ->parameters(['id' => $log->id()]),
                ]);
            }),
        ];
    }
}