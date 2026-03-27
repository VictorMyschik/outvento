<?php

declare(strict_types=1);

namespace App\Models\MessageLog;

use App\Models\ORM\ORM;
use Illuminate\Support\Facades\Blade;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class EmailLog extends ORM
{
    use AsSource;
    use Filterable;

    protected $table = 'email_logs';

    protected array $allowedSorts = [
        'id',
        'type',
        'email',
        'subject',
        'status',
        'created_at'
    ];

    public const null UPDATED_AT = null;

    protected $casts = [
        'sl' => 'json'
    ];

    public function getStatusDisplay(): string
    {
        return Blade::render($this->status ? '<x-orchid-icon class="text-success" path="check" />' : '<x-orchid-icon class="text-danger" path="ban" />');
    }
}
