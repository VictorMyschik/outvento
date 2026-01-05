<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Notifications;

use App\Models\MessageLog\EmailLog;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class EmailLogListLayout extends Table
{
    protected $target = 'list';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')->sort(),
            TD::make('type', 'Тип')->render(function (EmailLog $emailLog) {
                return $emailLog->getType()->getLabel();
            })->sort(),
            TD::make('', 'Просмотреть')->render(function (EmailLog $emailLog) {
                return ModalToggle::make('тело письма')
                    ->icon('eye')
                    ->modalTitle($emailLog->getSubject())
                    ->modal('view_body')
                    ->parameters(['id' => $emailLog->id()]);
            }),
            TD::make('email', 'Email')->sort(),
            TD::make('subject', 'Тема письма')->sort(),
            TD::make('status', 'Статус')->active()->sort(),
            TD::make('error', 'Ошибка')->width(500)->sort(),
            TD::make('created_at', 'Дата')->render(fn($group) => $group->created_at->format('d/m/Y H:i:s'))->sort(),

            TD::make('#', 'Действия')->render(function (EmailLog $emailLog) {
                return DropDown::make()->icon('options-vertical')->list([
                    Button::make('Resend')
                        ->confirm('Resend email?')
                        ->icon('reload')
                        ->method('resendEmail')
                        ->parameters(['id' => $emailLog->id()]),
                    Button::make('Delete')
                        ->confirm('Delete row?')
                        ->icon('trash')
                        ->method('deleteRow')
                        ->parameters(['id' => $emailLog->id()]),
                ]);
            }),
        ];
    }
}