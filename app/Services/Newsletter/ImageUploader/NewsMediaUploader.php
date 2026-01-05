<?php

declare(strict_types=1);

namespace App\Services\Newsletter\ImageUploader;

use App\Models\News\NewsMedia;
use App\Services\Newsletter\Enum\MediaType;
use App\Services\Newsletter\Enum\RelationMediaType;
use App\Services\Newsletter\NewsRepositoryInterface;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;

final readonly class NewsMediaUploader
{
    public function __construct(
        private Filesystem              $filesystem,
        private NewsRepositoryInterface $imageRepository,
        private array                   $storageConfig
    ) {}

    public function uploadMedia(UploadedFile $image, int $newsId, RelationMediaType $type): NewsMedia
    {
        $fileName = uniqid((string)time()) . $this->getImageExtensionByType($image->getMimeType());
        $baseDir = $this->storageConfig[$type->value];
        $filePathWithName = $baseDir . '/' . $newsId . '/' . $fileName;

        $this->filesystem->put($filePathWithName, $image->getContent());

        $this->imageRepository->addNewsMedia(MediaType::Image, $type, $newsId, $filePathWithName);

        return $this->imageRepository->getNewsMedia($type, $newsId);
    }

    public function deleteMedia(RelationMediaType $type, int $newsId): void
    {
        $image = $this->imageRepository->getNewsMedia($type, $newsId);
        $image && $this->filesystem->delete($image->path);
        $image && $image->delete();
    }

    private function getImageExtensionByType(?string $type): string
    {
        return match ($type) {
            'image/pjpeg', 'image/jpeg' => '.jpg',
            'image/x-png', 'image/png' => '.png',
            'image/gif' => '.gif',
            'image/svg+xml' => '.svg',
            default => '',
        };
    }
}
