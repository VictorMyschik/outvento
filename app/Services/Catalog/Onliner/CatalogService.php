<?php

declare(strict_types=1);

namespace App\Services\Catalog\Onliner;

use App\Models\Catalog\CatalogAttribute;
use App\Models\Catalog\CatalogAttributeValue;
use App\Models\Catalog\CatalogGood;
use App\Models\Catalog\CatalogGroup;
use App\Models\Catalog\CatalogGroupAttribute;
use App\Models\Catalog\CatalogImage;
use App\Models\Catalog\Manufacturer;
use App\Models\Orchid\Attachment;
use App\Repositories\Catalog\Onliner\CatalogCacheRepository;
use App\Repositories\Catalog\Onliner\CatalogRepositoryInterface;
use App\Services\Catalog\Enum\CatalogImageTypeEnum;
use App\Services\Catalog\Enum\ImageTypeEnum;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final readonly class CatalogService
{
    public function __construct(
        private CatalogRepositoryInterface $repository,
        private ImageUploaderInterface     $imageUploader,
        private CatalogCacheRepository     $cacheRepository,
    ) {}

    public function saveGoodImage(CatalogGood $good, Attachment $attachment, CatalogImageTypeEnum $type): int
    {
        $path = Storage::path($attachment->getFullPath());

        if (!file_exists($path) || !is_file($path)) {
            Attachment::where('hash', $attachment->getHash())->delete();
            throw new Exception('Ошибка при загрузке файла. Попробуйте ещё раз.');
        }
        $uploadedFile = new UploadedFile($path, $attachment->getOriginalName(), $attachment->getMime(), null, true);

        $this->imageUploader->uploadImage($uploadedFile, $good->id(), $type);

        $this->deleteAttachment($attachment);

        return 999999;
    }

    private function deleteAttachment(Attachment $attachment): void
    {
        Attachment::where('hash', $attachment->getHash())->delete();
        Storage::delete($attachment->getFullPath());
    }

    public function isGoodExist(string $stringId): bool
    {
        return $this->repository->isGoodExist($stringId);
    }

    public function getCatalogGroupList(): array
    {
        return $this->cacheRepository->getCatalogGroupList();
    }

    public function getCatalogGroupById(int $id): CatalogGroup
    {
        return $this->repository->getCatalogGroupById($id);
    }

    public function saveGood(int $id, array $data): int
    {
        return $this->repository->saveGood($id, $data);
    }

    public function getGroupAttributeOrCreateNew(int $id, string $groupName, int $sortOrder): CatalogGroupAttribute
    {
        return $this->repository->getGroupAttributeOrCreateNew($id, $groupName, $sortOrder);
    }

    public function getCatalogAttributeOrCreateNew(CatalogGroupAttribute $group, string $title): CatalogAttribute
    {
        return $this->repository->getCatalogAttributeOrCreateNew($group, $title);
    }

    public function getCatalogAttributeValueOrCreateNew(CatalogAttribute $attribute, ?string $value): CatalogAttributeValue
    {
        return $this->repository->getCatalogAttributeValueOrCreateNew($attribute, $value);
    }

    public function createGoodAttributes(array $goodAttributes): void
    {
        $this->repository->createGoodAttributes($goodAttributes);
    }

    public function getManufacturerOrCreateNew(array $data): Manufacturer
    {
        return $this->repository->getManufacturerOrCreateNew($data);
    }

    public function deleteGood(int $id): void
    {
        $this->imageUploader->deleteImagesWithModels($id, ImageTypeEnum::Good);
        $this->repository->deleteGood($id);
    }

    public function getGoodLogo(int $goodId): ?CatalogImage
    {
        return $this->repository->getGoodLogo($goodId);
    }

    public function getManufacturerName(int $manufacturerId): ?string
    {
        return $this->repository->getManufacturer($manufacturerId)?->name ?? null;
    }

    public function hasGoodByStringId(string $intId): bool
    {
        return $this->repository->hasGoodByStringId($intId);
    }

    public function deleteManufacturer(int $manufacturerId): void
    {
        $this->repository->deleteManufacturer($manufacturerId);
    }

    public function deleteCatalogType(int $typeId): void
    {
        $this->repository->deleteCatalogGroup($typeId);
    }

    public function saveCatalogGroup(int $id, array $type): void
    {
        $this->repository->saveCatalogGroup($id, $type);
    }

    public function getGoodById(int $id): ?CatalogGood
    {
        return $this->repository->getGoodById($id);
    }

    public function getGoodImages(int $goodId): array
    {
        return $this->repository->getGoodImages($goodId);
    }

    public function getGoodImageById(int $catalogImageId): ?CatalogImage
    {
        return $this->repository->getGoodImageById($catalogImageId);
    }

    public function deleteImage(int $imageId): void
    {
        $this->imageUploader->deleteImageById($imageId);
    }

    public function getAPIGoodAttributes(int $goodId): array
    {
        return $this->repository->getGoodAttributes($goodId);
    }

    public function getGoodAttributes(int $goodId): array
    {
        $out = array();

        foreach ($this->repository->getGoodAttributes($goodId) as $item) {
            $attributeGroupName = $item->group_name;
            $attributeGroupSort = $item->group_sort;

            $id = $item->good_attribute_id;

            $out[$attributeGroupName]['data'][] = array(
                'id'          => $id,
                'name'        => $item->attribute_name,
                'value'       => $item->attribute_value,
                'sort'        => $item->attribute_sort,
                'bool'        => $item->bool_value,
                'description' => $item->attribute_description,
            );

            $out[$attributeGroupName]['sort'] = $attributeGroupSort;
        }

        return $out;
    }

    public function getGoodsByIds(array $ids): array
    {
        return $this->repository->getGoodsByIds($ids);
    }

    public function saveManufacturer(int $id, $data): int
    {
        return $this->repository->saveManufacturer($id, $data);
    }

    public function getManufacturer(int $id): ?Manufacturer
    {
        return $this->repository->getManufacturer($id);
    }

    public function deleteAllGoodPhoto(int $good_id): void
    {
        $this->imageUploader->deleteImagesWithModels($good_id, ImageTypeEnum::Good);
    }
}
