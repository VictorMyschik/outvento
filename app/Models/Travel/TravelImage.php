<?php

declare(strict_types=1);

namespace App\Models\Travel;

use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\Lego\Fields\NameFieldTrait;
use App\Models\Lego\Fields\UserFieldTrait;
use App\Models\ORM\ORM;
use App\Services\Travel\Enum\ImageType;
use Illuminate\Support\Facades\Storage;

class TravelImage extends ORM
{
    use NameFieldTrait;
    use DescriptionNullableFieldTrait;
    use UserFieldTrait;

    protected $table = 'travel_images';

    public function getType(): ImageType
    {
        return ImageType::from($this->kind);
    }

    public function getTravel(): Travel
    {
        return Travel::loadByOrDie($this->travel_id);
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function getOriginalName(): string
    {
        return $this->original_name;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function getPath(): string
    {
        return '/travels/' . $this->travel_id . '/images';
    }

    public function getUrl(): string
    {
        return Storage::url($this->getPath() . '/' . $this->getName());
    }
}
