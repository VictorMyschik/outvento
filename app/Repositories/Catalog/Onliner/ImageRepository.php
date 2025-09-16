<?php

declare(strict_types=1);

namespace App\Repositories\Catalog\Onliner;

use App\Models\Catalog\CatalogImage;
use App\Repositories\DatabaseRepository;
use App\Services\Catalog\Onliner\DTO\ImageDTO;

final readonly class ImageRepository extends DatabaseRepository implements ImageRepositoryInterface
{
    public function addImage(ImageDTO $dto): CatalogImage
    {
        return CatalogImage::firstOrCreate([
            'hash'    => $dto->hash,
            'good_id' => $dto->good_id,
        ], $dto->toArray());
    }

    public function getImageById(int $imageId): ?CatalogImage
    {
        return CatalogImage::loadBy($imageId);
    }

    public function getImageListByGoodId(int $goodId): array
    {
        return CatalogImage::where('good_id', $goodId)->get()->all();
    }

    public function deleteImage(CatalogImage $image): void
    {
        $image->delete();
    }

    public function addImagesBulk(array $imageDtos): void
    {
        CatalogImage::insert($imageDtos);
    }
}
