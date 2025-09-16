<?php

declare(strict_types=1);

namespace App\Repositories\Catalog\Onliner;

use App\Models\Catalog\CatalogImage;

interface ImageRepositoryInterface
{
    public function getImageById(int $imageId): ?CatalogImage;

    public function getImageListByGoodId(int $goodId): array;

    public function deleteImage(CatalogImage $image): void;

    public function addImagesBulk(array $imageDtos): void;
}
