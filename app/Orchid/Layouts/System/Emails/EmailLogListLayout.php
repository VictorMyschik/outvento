<?php

namespace App\Orchid\Layouts\System\Emails;

use App\Models\Email\EmailLog;
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
            TD::make('email', 'Email')->sort(),
            TD::make('subject', 'Тема письма')->sort(),
            TD::make('status', 'Статус')->active()->sort(),
            TD::make('error', 'Ошибка')->width(500)->sort(),
            TD::make('created_at', 'Дата')->render(fn($group) => $group->created_at?->format('d.m.Y H:i:s')),

            TD::make('#', 'Действия')->render(function (EmailLog $emailLog) {
                return DropDown::make()->icon('options-vertical')->list([
                    ModalToggle::make('тело письма')
                        ->icon('eye')
                        ->modalTitle('Тело письма для ' . $emailLog->getEmail())
                        ->modal('view_body')
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