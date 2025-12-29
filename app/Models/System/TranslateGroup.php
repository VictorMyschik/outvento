<?php

declare(strict_types=1);

namespace App\Models\System;

use App\Models\ORM\ORM;
use App\Services\Language\Enum\TranslateGroupEnum;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class TranslateGroup extends ORM
{
    use AsSource;
    use Filterable;

    const null UPDATED_AT = null;

    protected $table = 'translate_groups';

    protected $fillable = array(
        'translate_id',
        'group',
    );

    protected array $allowedSorts = [
        'id',
        'translate_id',
        'group_code'
    ];

    public function getGroup(): TranslateGroupEnum
    {
        return TranslateGroupEnum::from($this->group);
    }
}
