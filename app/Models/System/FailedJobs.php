<?php

namespace App\Models\System;

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
        'exception',
    ];

    protected $casts = [
        'failed_at' => 'datetime',
    ];
}
