<?php

declare(strict_types=1);

namespace App\Models\Travel;

use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\Lego\Fields\KindFieldTrait;
use App\Models\Lego\Fields\NameFieldTrait;
use App\Models\Lego\Fields\UserFieldTrait;
use App\Models\ORM\ORM;
use Illuminate\Support\Facades\Storage;

class TravelImage extends ORM
{
    use NameFieldTrait;
    use KindFieldTrait;
    use DescriptionNullableFieldTrait;
    use UserFieldTrait;

    protected $table = 'travel_images';

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

    public function getLocalPath(): string
    {
        return $this->getTravel()->getDirNameForImages() . DIRECTORY_SEPARATOR . $this->name;
    }

    private function deleteImageFromStorage(): void
    {
        $imagePath = $this->getTravel()->getDirNameForImages() . '/' . $this->getName();
        $imagePath = str_replace('storage/', '', $imagePath);
        Storage::delete($imagePath);
    }
}
