<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Migration extends Model
{
    use AsSource;
    use Filterable;

    protected $table = 'migrations';

    protected array $allowedSorts = [
        'id',
        'migration',
        'batch',
    ];
}
