<?php

declare(strict_types=1);

namespace App\Services\Constructor\DTO;

use App\Models\User;
use App\Repositories\Constructor\Storage\Enum\StorageFileTypeEnum;
use Illuminate\Http\UploadedFile;

final readonly class SlideDTO
{
    public function __construct(
        public StorageFileTypeEnum $type,
        public UploadedFile        $image,
        public int                 $slider_id,
        public ?string             $display_name,
        public int                 $sort,
        public ?string             $alt,
        public User                $user,
    ) {}
}