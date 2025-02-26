<?php

namespace App\Orchid\Layouts\System;

use App\Models\Job;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class JobsListLayout extends Table
{
    protected $target = 'jobs';

    public function striped(): bool
    {
        return true;
    }

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')->sort(),
            TD::make('queue', 'Queue')->class('text-nowrap')->sort(),
            TD::make('payload', 'Payload')->render(function (Job $job) {
                return ModalToggle::make('Show')
                    ->icon('eye')
                    ->modal('show')
                    ->modalTitle('Payload')
                    ->asyncParameters(['id' => $job->id()]);
            })->width(500),

            TD::make('reserved_at', 'Reserved')->sort()->render(fn(Job $job) => $job->reserved_at?->format('H:i:s d.m.Y')),
            TD::make('available_at', 'Available')->sort()->render(fn(Job $job) => $job->available_at?->format('H:i:s d.m.Y')),
            TD::make('created_at', 'Created')->sort()->render(fn(Job $job) => $job->created_at?->format('H:i:s d.m.Y')),

            TD::make('#', 'Действия')->render(function (Job $job) {
                return DropDown::make()->icon('options-vertical')->list([
                /*    Button::make('run')
                        ->icon('play')
                        ->method('runJob')
                        ->parameters(['job_id' => $job->id()]),*/

                    Button::make('Delete')
                        ->confirm('Delete this job?')
                        ->icon('trash')
                        ->method('deleteJob')
                        ->parameters(['job_id' => $job->id()]),
                ]);
            }),
        ];
    }

    protected function subNotFound(): string
    {
        return 'Jobs not found';
    }
}
