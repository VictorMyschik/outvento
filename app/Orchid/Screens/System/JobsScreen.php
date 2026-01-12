<?php

declare(strict_types=1);

namespace App\Orchid\Screens\System;

use App\Models\Job;
use App\Orchid\Filters\System\JobsFilter;
use App\Orchid\Layouts\Lego\ShowLayout;
use App\Orchid\Layouts\System\JobsListLayout;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class JobsScreen extends Screen
{
    public string $name = 'Jobs';

    public function commandBar(): iterable
    {
        return [
            Button::make('Delete all')
                ->icon('trash')
                ->confirm('Delete all jobs?')
                ->method('deleteAllJobs'),
        ];
    }

    public function query(): iterable
    {
        return ['jobs' => JobsFilter::runQuery()];
    }

    public function layout(): iterable
    {
        return [
            JobsListLayout::class,
            Layout::modal('show', ShowLayout::class)->async('asyncGetPayload')->size(Modal::SIZE_XL),
            Layout::modal('show-exception', ShowLayout::class)->async('asyncGetException')->size(Modal::SIZE_XL),
        ];
    }

    public function asyncGetPayload(int $id = 0): array
    {
        return [
            'job' => unserialize(
                json_decode((Job::findOrFail($id))->payload)->data->command,
                ['allowed_classes' => false]
            )
        ];
    }

    public function asyncGetException(int $id = 0): array
    {
        return [
            'job' => (Job::findOrFail($id))->exception,
        ];
    }

    public function deleteJob(int $job_id): void
    {
        Job::where('id', $job_id)->delete();
    }

    public function deleteAllJobs(): void
    {
        Job::truncate();
    }
}
