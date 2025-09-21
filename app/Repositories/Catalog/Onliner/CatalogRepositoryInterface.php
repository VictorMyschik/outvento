<?php

declare(strict_types=1);

namespace App\Repositories\Catalog\Onliner;

use App\Models\Catalog\CatalogAttribute;
use App\Models\Catalog\CatalogAttributeValue;
use App\Models\Catalog\CatalogGood;
use App\Models\Catalog\CatalogGoodAttribute;
use App\Models\Catalog\CatalogGroup;
use App\Models\Catalog\CatalogGroupAttribute;
use App\Models\Catalog\CatalogImage;
use App\Models\Catalog\Manufacturer;

interface CatalogRepositoryInterface
{
    public function isGoodExist(string $stringId): bool;

    public function getCatalogGroupList(): array;

    public function getCatalogGroupById(int $id): CatalogGroup;

    public function saveGood(int $id, array $data): int;

    public function getGroupAttributeOrCreateNew(int $groupId, string $groupName, int $sortOrder): CatalogGroupAttribute;

    public function getCatalogAttributeOrCreateNew(CatalogGroupAttribute $group, string $title): CatalogAttribute;

    public function getCatalogAttributeValueOrCreateNew(CatalogAttribute $attribute, ?string $value): CatalogAttributeValue;

    public function createGoodAttributes(array $goodAttributes): void;

    public function getManufacturerOrCreateNew(array $data): Manufacturer;

    public function deleteGood(int $id): void;

    public function getGoodLogo(int $goodId): ?CatalogImage;

    public function getManufacturer(int $id): ?Manufacturer;

    public function hasGoodByStringId(string $stringId): bool;

    public function deleteManufacturer(int $manufacturerId): void;

    public function deleteCatalogGroup(int $groupId): void;

    public function saveCatalogGroup(int $id, array $data): void;

    public function getGoodById(int $id): ?CatalogGood;

    public function getGoodImages(int $goodId): array;

    public function getGoodImageById(int $catalogImageId): ?CatalogImage;

    public function getGoodAttributes(int $goodId): array;

    public function getGoodsByIds(array $ids): array;

    public function saveManufacturer(int $id, $data): int;

    public function saveGoodImage(int $id, array $data): int;

    public function deleteAttribute(int $attributeId): void;

    public function getAttributeById(int $attributeId): ?CatalogAttribute;

    public function saveAttributeValue(int $attributeValueId, array $data): int;

    public function saveAttribute(int $attributeId, array $data): int;

    public function deleteAllGoodAttributes(int $goodId): void;

    public function addGoodAttribute(int $goodAttributeId, int $goodId, int $attributeValueId, ?bool $boolValue): int;
}
