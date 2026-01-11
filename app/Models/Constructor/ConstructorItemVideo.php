<?php

declare(strict_types=1);

namespace App\Models\Constructor;

use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\Lego\Fields\SortFieldTrait;
use App\Models\Lego\Fields\TitleNullableFieldTrait;
use App\Models\ORM\ORM;

class ConstructorItemVideo extends ORM
{
    use SortFieldTrait;
    use DescriptionNullableFieldTrait;
    use TitleNullableFieldTrait;

    protected $table = 'constructor_item_videos';
}