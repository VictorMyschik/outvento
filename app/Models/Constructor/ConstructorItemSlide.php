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

    public function getSliderID(): int
    {
        return $this->slider_id;
    }

    public function setSliderID(int $value): void
    {
        $this->slider_id = $value;
    }

    public function setAlt(?string $value): void
    {
        $this->alt = $value;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $value): void
    {
        $this->path = $value;
    }

    public function getFileName(): string
    {
        return $this->file_name;
    }

    public function setFileName(string $value): void
    {
        $this->file_name = $value;
    }

    public function setDisplayName(?string $value): void
    {
        $this->display_name = $value;
    }

    public function getUrl(): string
    {
        return Storage::url($this->getPath() . '/' . $this->getFileName());
    }

    public function getFilePathWithName(): string
    {
        return $this->getPath() . '/' . $this->getFileName();
    }
}