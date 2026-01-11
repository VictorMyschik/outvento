<?php

declare(strict_types=1);

namespace App\Repositories\References;

use App\Services\References\Enum\ImageTypeEnum;
use App\Services\References\ImageRepositoryInterface;
use Illuminate\Contracts\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ImageRepository implements ImageRepositoryInterface
{
    private const string IMAGE_PATH = '/images/reference';

    public function __construct(private Filesystem $filesystem) {}

    public function deleteImage(string $path): void
    {
        $this->filesystem->delete($path);
    }

    public function saveImage(ImageTypeEnum $enum, UploadedFile $file): string
    {
        $path = $this->getPath($enum);
        $name = (string)crc32((string)microtime()) . '.' . $file->getExtension();
        $this->filesystem->putFileAs($path, $file, $name);

        return $path . '/' . $name;
    }

    private function getPath(ImageTypeEnum $enum): string
    {
        return self::IMAGE_PATH . '/' . $enum->value;
    }
}
