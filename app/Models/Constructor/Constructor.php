<?php

declare(strict_types=1);

namespace App\Models\Constructor;

use App\Models\Lego\Fields\SortFieldTrait;
use App\Models\Lego\Fields\TitleFieldTrait;
use App\Models\ORM\ORM;

class Constructor extends ORM
{
    use TitleFieldTrait;
    use SortFieldTrait;

    protected $table = 'constructors';
}