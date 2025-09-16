<?php

declare(strict_types=1);

namespace App\Services\Catalog\API;

use App\Http\Controllers\Api\V1\Catalog\Response\CatalogGoodResponse;
use App\Http\Controllers\Api\V1\Catalog\Response\Components\AttributeComponent;
use App\Http\Controllers\Api\V1\Catalog\Response\Components\AttributeGroupComponent;
use App\Http\Controllers\Api\V1\Catalog\Response\Components\AttributeValueComponent;
use App\Http\Controllers\Api\V1\Catalog\Response\Components\CatalogGroupComponent;
use App\Http\Controllers\Api\V1\Catalog\Response\Components\ManufacturerComponent;
use App\Models\Catalog\Onliner\OnCatalogGood;
use App\Services\Catalog\Onliner\CatalogService;
use App\Services\Elasticsearch\ESService;

final readonly class CatalogAPIResponse implements CatalogAPIInterface
{
    public function __construct(private CatalogService $repository, private ESService $elastic) {}

    public function searchGoods(string $query, int $limit): array
    {
        $esResult = $this->elastic->searchGoods($query, $limit);

        $hits = $esResult['hits']['hits'] ?? [];

        $ids = array_map(
            fn(array $hit) => (int)$hit['_source']['id'],
            $hits
        );

        $goodList = $this->repository->getGoodsByIds($ids);

        $goods = [];
        foreach ($ids as $id) {
            $goods[] = $this->buildGoodResponse($goodList[$id]);
        }

        return $goods;
    }

    private function buildGoodResponse(OnCatalogGood $good): CatalogGoodResponse
    {
        return new CatalogGoodResponse(
            id: $good->id(),
            group: $this->buildGroupComponent($good),
            prefix: $good->getPrefix(),
            name: $good->getName(),
            short_info: $good->getShortInfo(),
            description: $good->getDescription(),
            manufacturer: $this->buildManufacturerComponent($good),
            parent_good_id: $good->getParentGoodId(),
            is_certification: $good->isCertification(),
            attributes: $this->buildGroupAttributesComponent($this->repository->getGoodAttributes($good->id())),
            created_at: $good->created_at->format(DATE_ATOM),
            updated_at: $good->updated_at?->format(DATE_ATOM),
        );
    }

    private function buildGroupAttributesComponent(array $attributes): array
    {
        $out = [];

        foreach ($attributes as $groupName => $group) {
            $out[] = new AttributeGroupComponent(
                title: $groupName,
                sort: $group['sort'],
                attributes: $this->buildAttributesComponent($group)
            );
        }

        return $out;
    }

    private function buildAttributesComponent(array $group): array
    {
        $out = [];

        foreach ($group['data'] as $attribute) {
            $out[] = new AttributeComponent(
                id: $attribute['id'],
                name: $attribute['name'],
                description: $attribute['description'],
                sort: $attribute['sort'],
                value: new AttributeValueComponent(
                    value: $attribute['value'],
                    bool_value: $attribute['bool'],
                ),
            );
        }

        return $out;
    }

    private function buildManufacturerComponent(OnCatalogGood $good): ?ManufacturerComponent
    {
        $manufacturer = $good->getManufacturer();
        if (!$manufacturer) {
            return null;
        }

        return new ManufacturerComponent(
            id: $manufacturer->id(),
            name: $manufacturer->getName(),
            address: $manufacturer->getAddress(),
        );
    }

    private function buildGroupComponent(OnCatalogGood $good): CatalogGroupComponent
    {
        $group = $good->getGroup();

        return new CatalogGroupComponent(
            id: $good->getGroupID(),
            title: $group->getName(),
        );
    }
}
