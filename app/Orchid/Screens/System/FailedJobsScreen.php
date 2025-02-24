<?php

declare(strict_types=1);

namespace App\Orchid\Screens\System;

use App\Models\System\FailedJobs;
use App\Orchid\Filters\System\FailedJobsFilter;
use App\Orchid\Layouts\Lego\ShowLayout;
use App\Orchid\Layouts\System\FailedJobsListLayout;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class FailedJobsScreen extends Screen
{
    public function name(): ?string
    {
        return 'Failed Jobs';
    }

    public function commandBar(): iterable
    {
        return [
            // deleteAllFailedJobs
            Button::make('Delete all')
                ->icon('trash')
                ->confirm('Delete all failed jobs?')
                ->method('deleteAllFailedJobs'),
        ];
    }

    public function query(): iterable
    {
        return ['failed-jobs' => FailedJobsFilter::runQuery()];
    }

    public function layout(): iterable
    {
        return [
            FailedJobsListLayout::class,
            Layout::modal('show', ShowLayout::class)->async('asyncGetPayload')->size(Modal::SIZE_XL),
            Layout::modal('show-exception', ShowLayout::class)->async('asyncGetException')->size(Modal::SIZE_XL),
        ];
    }

    public function asyncGetPayload(int $id = 0): array
    {
        return [
            'job' => unserialize(json_decode((FailedJobs::findOrFail($id))->payload)->data->command, ['allowed_classes' => false])
        ];
    }

    public function asyncGetException(int $id = 0): array
    {
        return [
            'job' => (FailedJobs::findOrFail($id))->exception,
        ];
    }

    public function deleteFailedJob(int $failed_job_id): void
    {
        FailedJobs::where('id', $failed_job_id)->delete();
    }

    public function deleteAllFailedJobs(): void
    {
        FailedJobs::truncate();
    }

    public function retryFailedJob(int $failed_job_id): void
    {
        $job = FailedJobs::findOrFail($failed_job_id);
        dispatch($job->payload);
        $job->delete();
    }
}
