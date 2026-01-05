<?php

declare(strict_types=1);

namespace App\Services\Newsletter\ImageUploader;

use Illuminate\Http\UploadedFile;

interface ImageUploaderInterface
{
    public function uploadImage(UploadedFile $image, int $objectID, ImageTypeEnum $type): Image;
}
