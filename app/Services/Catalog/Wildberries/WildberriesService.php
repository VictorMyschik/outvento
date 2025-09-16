<?php

declare(strict_types=1);

namespace App\Services\Catalog\Wildberries;

use App\Models\Catalog\Wildberries\WBCatalogGood;
use App\Repositories\Catalog\Wildberries\WBGoodsInterface;
use App\Services\Catalog\Enum\ImageTypeEnum;

final readonly class WildberriesService
{
    public function __construct(
        private WBGoodsInterface       $repository,
        private ImageUploaderInterface $imageUploader,
    ) {}

    public function getGoodById(int $goodId): WBCatalogGood
    {
        return $this->repository->getGoodById($goodId);
    }

    public function deleteGood(int $id): void
    {
        $this->repository->deleteGood($id);
    }

    public function getGoodImages(int $goodId): array
    {
        return $this->repository->getGoodImages($goodId);
    }

    public function deleteAllGoodPhoto(int $goodId): void
    {
        $this->imageUploader->deleteImagesWithModels($goodId, ImageTypeEnum::Good);
    }

    public function deleteImage(int $imageId): void
    {
        $this->imageUploader->deleteImageById($imageId);
    }
}