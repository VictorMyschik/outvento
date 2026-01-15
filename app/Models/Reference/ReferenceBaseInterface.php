<?php

namespace App\Models\Reference;

interface ReferenceBaseInterface
{
    public function getImagePath(): ?string;

    public function getImageUrl(): ?string;
}
