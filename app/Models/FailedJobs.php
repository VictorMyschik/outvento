<?php

namespace App\Models;

use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class FailedJobs extends ORM
{
    use AsSource;
    use Filterable;

    protected $table = 'failed_jobs';

    protected array $allowedSorts = [
        'id',
        'connection',
        'failed_at',
        'queue',
    ];
}
