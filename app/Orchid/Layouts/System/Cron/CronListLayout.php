<?php

namespace App\Orchid\Layouts\System\Cron;

use App\Models\System\Cron;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class CronListLayout extends Table
{
    protected $target = 'list';

    protected function columns(): iterable
    {
        $rows = [
            TD::make('id', 'ID')->sort(),
            TD::make('active', 'Активно')->sort()->active(),
            TD::make('period', 'Период')->sort(),
            TD::make('cron_key', 'Наименование')->render(fn(Cron $cron) => $cron->getCronKey()->getLabel())->sort(),
            TD::make('description', 'Описание')->width('50%')->defaultHidden()->sort(),
            TD::make('last_work', 'Last Work')
                ->render(fn(Cron $cron) => $cron->getLastWork())
                ->sort()
                ->defaultHidden(),
            TD::make('created_at', 'Created')
                ->render(fn(Cron $cron) => $cron->created_at->format('d.m.Y H:i:s'))
                ->sort()
                ->defaultHidden(),
            TD::make('updated_at', 'Updated')
                ->render(fn(Cron $cron) => $cron->updated_at->format('d.m.Y H:i:s'))
                ->sort()
                ->defaultHidden(),
        ];

        $rows[] = TD::make('#', 'Действия')
            ->align(TD::ALIGN_CENTER)
            ->width('100px')
            ->render(function (Cron $cron) {
                return DropDown::make()->icon('options-vertical')->list([
                    ModalToggle::make('Edit')
                        ->icon('pencil')
                        ->modal('cron_modal')
                        ->modalTitle('Edit Cron Job')
                        ->method('saveCron')
                        ->asyncParameters(['id' => $cron->id()]),

                    Button::make('Delete')
                        ->icon('trash')
                        ->confirm('This Cron will be removed permanently.')
                        ->method('remove', ['id' => $cron->id()]),

                    Button::make('Run')
                        ->icon('refresh')
                        ->confirm('Run this cron job')
                        ->method('run', ['id' => $cron->id()]),
                ]);
            });

        return $rows;
    }
}
