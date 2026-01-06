<?php

declare(strict_types=1);

namespace App\Services\Newsletter\ImageUploader;

use App\Models\News\NewsMedia;
use App\Services\Newsletter\ImageUploader\Enum\NewsFileType;
use App\Services\Newsletter\ImageUploader\Enum\NewsMediaType;
use App\Services\Newsletter\NewsRepositoryInterface;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;

final readonly class NewsMediaUploader
{
    public function __construct(
        private Filesystem              $filesystem,
        private NewsRepositoryInterface $imageRepository,
        private string                  $basePath,
    ) {}

    public function uploadMedia(UploadedFile $image, int $newsId, NewsMediaType $mediaType, NewsFileType $fileType): NewsMedia
    {
        $filePathWithName = $this->getPathByType($image, $newsId, $mediaType);

        $this->filesystem->put($filePathWithName, $image->getContent());

        $id = $this->imageRepository->addNewsMedia(newsId: $newsId, fileType: $fileType, mediaType: $mediaType, path: $filePathWithName);

        return $this->imageRepository->getMediaById($id);
    }

    public function deleteMedia(NewsMediaType $type, int $newsId): void
    {
        $image = $this->imageRepository->getNewsMedia($type, $newsId);
        $image && $this->filesystem->delete($image->path);
        $image && $image->delete();
    }

    private function getPathByType(UploadedFile $image, int $newsId, NewsMediaType $type): string
    {
        return match ($type) {
            NewsMediaType::Logo => $this->basePath . '/' . $newsId . '/' . $image->getClientOriginalName(),
        };
    }
}
