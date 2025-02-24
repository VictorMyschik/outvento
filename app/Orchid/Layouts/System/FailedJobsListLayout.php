<?php

namespace App\Orchid\Layouts\System;

use App\Models\System\FailedJobs;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class FailedJobsListLayout extends Table
{
    protected $target = 'failed-jobs';

    public function striped(): bool
    {
        return true;
    }

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')->sort(),
            TD::make('uuid', 'Unique ID')->class('text-nowrap')->width(100),
            TD::make('queue', 'Queue')->class('text-nowrap')->sort(),
            TD::make('payload', 'Payload')->render(function (FailedJobs $job) {
                return ModalToggle::make('Show')
                    ->icon('eye')
                    ->modal('show')
                    ->modalTitle('Payload')
                    ->asyncParameters(['id' => $job->id()]);
            })->width(500),

            TD::make('exception', 'Exception')->render(function (FailedJobs $job) {
                if (preg_match('/^Exception: (.*?) in /', $job->exception, $matches)) {
                    $message = $matches[1];
                }

                return ModalToggle::make($message)
                    ->icon('eye')
                    ->modal('show-exception')
                    ->modalTitle('Payload')
                    ->asyncParameters(['id' => $job->id()]);
            }),

            TD::make('failed_at', 'Failed')->sort()
                ->render(fn(FailedJobs $job) => $job->failed_at->format('H:i:s d.m.Y')),

            TD::make('#', 'Действия')->render(function (FailedJobs $job) {
                return DropDown::make()->icon('options-vertical')->list([
                    Button::make('Retry')
                        ->confirm('Retry this job?')
                        ->icon('action-undo')
                        ->method('retryFailedJob')
                        ->parameters(['failed_job_id' => $job->id()]),

                    Button::make('Delete')
                        ->confirm('Delete this job?')
                        ->icon('trash')
                        ->method('deleteFailedJob')
                        ->parameters(['failed_job_id' => $job->id()]),
                ]);
            }),
        ];
    }

    protected function subNotFound(): string
    {
        return 'Jobs not found';
    }
}
