<?php

declare(strict_types=1);

namespace App\Models\News;

use App\Models\Lego\Fields\SortFieldTrait;
use App\Models\ORM\ORM;

class NewsAdditional extends ORM
{
    use SortFieldTrait;

    protected $table = 'news_additional';

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
