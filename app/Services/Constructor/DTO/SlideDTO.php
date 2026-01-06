<?php

declare(strict_types=1);

namespace App\Services\Constructor\DTO;

use Illuminate\Http\UploadedFile;

final readonly class SlideDTO
{
    public function __construct(
        public int           $constructorId,
        public int           $sliderId,
        public int           $slideId,
        public ?UploadedFile $image,
        public ?string       $displayName,
        public int           $sort,
        public ?string       $alt,
    ) {}
}