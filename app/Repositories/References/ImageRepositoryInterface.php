<?php

declare(strict_types=1);

namespace App\Repositories\References;


use App\Services\References\Enum\ImageTypeEnum;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ImageRepositoryInterface
{
    public function saveImage(ImageTypeEnum $enum, UploadedFile $file): string;

    public function deleteImage(string $path): void;
}
