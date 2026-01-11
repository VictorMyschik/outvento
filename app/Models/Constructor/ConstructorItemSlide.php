<?php

declare(strict_types=1);

namespace App\Models\Constructor;

use App\Models\Lego\Fields\SortFieldTrait;
use App\Models\ORM\ORM;
use Illuminate\Support\Facades\Storage;

class ConstructorItemSlide extends ORM
{
    use SortFieldTrait;

    protected $table = 'constructor_item_slides';

    public $timestamps = false;

    public function getPath(): string
    {
        return $this->path;
    }

    public function getDisplayName(): ?string
    {
        return $this->display_name;
    }

    public function getUrl(): string
    {
        return Storage::url($this->getPath());
    }
}