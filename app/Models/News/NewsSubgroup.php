<?php

declare(strict_types=1);

namespace App\Models\News;

use App\Models\Lego\Fields\CodeFieldTrait;
use App\Models\ORM\ORM;
use Orchid\Screen\AsSource;

class NewsSubgroup extends ORM
{
    use AsSource;
    use CodeFieldTrait;

    protected $table = 'news_subgroups';

    public function getGroup(): NewsGroup
    {
        return NewsGroup::loadBy((int)$this->group_id);
    }
}