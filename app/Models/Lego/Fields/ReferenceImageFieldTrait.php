<?php

namespace App\Models\Lego\Fields;

use Illuminate\Support\Facades\Storage;

trait ReferenceImageFieldTrait
{
    public function getImagePath(): ?string
    {
        return $this->image_path;
    }

    public function getImageUrl(): ?string
    {
        if ($this->image_path) {
            return Storage::url($this->getImagePath());
        }

        return null;
    }
}
