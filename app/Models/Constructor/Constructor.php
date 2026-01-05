<?php

declare(strict_types=1);

namespace App\Models\Constructor;

use App\Models\Lego\Fields\SortFieldTrait;
use App\Models\Lego\Fields\TitleFieldTrait;
use App\Models\News\NewsMedia;
use App\Models\ORM\ORM;
use App\Services\Newsletter\Enum\RelationMediaType;

class Constructor extends ORM
{
    use TitleFieldTrait;
    use SortFieldTrait;

    protected $table = 'constructors';

    public function getIcon(): ?NewsMedia
    {
        return NewsMedia::where('news_id', $this->id())->where('type', RelationMediaType::ConstructorBlockIcon->value)->first();
    }
}