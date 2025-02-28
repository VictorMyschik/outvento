<?php

declare(strict_types=1);

namespace App\Models\System;

use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Translate extends ORM
{
    use AsSource;
    use Filterable;

    const null UPDATED_AT = null;

    protected $table = 'translates';

    protected $fillable = array(
        'code',
        'ru',
        'en',
        'pl',
    );

    protected array $allowedSorts = [
        'code',
        'ru',
        'en',
        'pl',
    ];
}
