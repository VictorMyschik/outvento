<?php

declare(strict_types=1);

namespace App\Models\Email;

use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class EmailLog extends ORM
{
    use AsSource;
    use Filterable;

    public const null UPDATED_AT = null;

    protected $table = 'email_logs';

    protected array $allowedSorts = [
        'id',
        'type',
        'email',
        'subject',
        'status',
        'created_at'
    ];

    protected $updated_at = false;

    protected $casts = [
        'sl'         => 'json',
        'created_at' => 'datetime',
    ];

}