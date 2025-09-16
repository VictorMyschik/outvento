<?php

declare(strict_types=1);

namespace App\Services\Catalog\Wildberries;

use App\Models\Catalog\Wildberries\WBCatalogImage;
use App\Repositories\Catalog\Wildberries\ImageRepositoryInterface;
use App\Services\Catalog\Enum\CatalogImageTypeEnum;
use App\Services\Catalog\Enum\ImageExtensionEnum;
use App\Services\Catalog\Enum\ImageTypeEnum;
use App\Services\Catalog\Enum\MediaTypeEnum;
use App\Services\Catalog\Wildberries\DTO\ImageDTO;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

final readonly class ImageUploadService implements ImageUploaderInterface
{
    public function __construct(
        private Filesystem               $filesystem,
        private ImageRepositoryInterface $imageRepository,
        private array                    $storageConfig
    ) {}

    public function uploadImageByURL(int $goodId, string $imageUrl): void
    {
        try {
            $image = getimagesize($imageUrl);

            $fileName = $this->getImageNameByType($image['mime']);

            $path = $this->getPathToSave($goodId) . '/' . $fileName;

            $this->imageRepository->addImage(
                new ImageDTO(
                    good_id: $goodId,
                    original_url: $imageUrl,
                    path: $path,
                    hash: md5_file($imageUrl),
                    type: CatalogImageTypeEnum::PHOTO,
                    media_type: MediaTypeEnum::IMAGE,
                )
            );

            //$this->filesystem->put($path, file_get_contents($imageUrl));
        } catch (\Exception $e) {
            Log::error('Error upload image: ' . $e->getMessage(), ['good_id' => $goodId, 'image_url' => $imageUrl]);
        }
    }

    private function getPathToSave(int $goodId): string
    {
        return $this->storageConfig['images'] . '/' . $goodId;
    }

    public function uploadImage(UploadedFile $image, int $goodId, CatalogImageTypeEnum $type): WBCatalogImage
    {
        $path = $this->getPathToSave($goodId);
        $this->filesystem->put($path . '/' . $image->getClientOriginalName(), $image->getContent());

        return $this->imageRepository->addImage(
            new ImageDTO(
                good_id: $goodId,
                original_url: null,
                path: $path . '/' . $image->getClientOriginalName(),
                hash: md5_file($image->getPathname()),
                type: $type,
                media_type: $this->getMediaType($image->getClientMimeType()),
            )
        );
    }

    private function getMediaType(string $mime): MediaTypeEnum
    {
        if (in_array($mime, ImageExtensionEnum::getList())) {
            return MediaTypeEnum::IMAGE;
        }

        if ($mime === 'video/mp4') {
            return MediaTypeEnum::VIDEO;
        }

        throw new \Exception('Unknown media type');
    }

    public function deleteImagesWithModels(int $objectId, ImageTypeEnum $imageType): void
    {
        $images = match ($imageType) {
            ImageTypeEnum::Good => $this->imageRepository->getImageListByGoodId($objectId),
            default => throw new ModelNotFoundException($imageType->getLabel() . ' type unknown'),
        };

        if (empty($images)) {
            return;
        }

        $dir = '';
        /** @var WBCatalogImage[] $images */
        foreach ($images as $image) {
            $dir = $image->getPath();
            $this->deleteImage($image);
        }

        $dir && $this->filesystem->deleteDirectory($dir);
    }

    public function deleteFile(string $path): void
    {
        $this->filesystem->delete($path);
    }

    public function getImageNameByType(string $type): string
    {
        return uniqid((string)time()) . $this->getImageExtensionByType($type);
    }

    private function getImageExtensionByType(?string $type): string
    {
        return match ($type) {
            'image/pjpeg', 'image/jpeg' => '.jpg',
            'image/x-png', 'image/png' => '.png',
            'image/gif' => '.gif',
            'image/svg+xml' => '.svg',
            'image/webp' => '.webp',
            default => '',
        };
    }

    private function deleteImage(WBCatalogImage $image): void
    {
        $image->getPath() && $this->deleteFile($image->getPath());
        $this->imageRepository->deleteImage($image);
    }

    public function deleteImageById(int $id): void
    {
        $image = $this->imageRepository->getImageById($id);
        $image && $this->deleteImage($image);
    }
}
