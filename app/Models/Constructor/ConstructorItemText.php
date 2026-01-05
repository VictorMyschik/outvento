<?php

declare(strict_types=1);

namespace App\Models\Constructor;

use App\Models\Lego\Fields\SortFieldTrait;
use App\Models\ORM\ORM;

class ConstructorItemText extends ORM
{
    use SortFieldTrait;

    protected $table = 'constructor_item_texts';

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getText(): ?string
    {
        return $this->text;
    }
}