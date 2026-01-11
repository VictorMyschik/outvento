<?php

declare(strict_types=1);

namespace App\Services\Elasticsearch;

use App\Events\ClearCacheEvent;
use App\Models\Catalog\CatalogGood;
use App\Services\Catalog\API\CatalogAPICache;
use Illuminate\Support\Facades\Log;

final readonly class ESService
{
    private const string INDEX = 'catalog';

    private const array FIELDS = [
        'id',
        'prefix',
        'name',
        'short_info',
        'description',
        'manufacturer',
    ];

    public function __construct(private ESClient $client) {}

    public function addGood(CatalogGood $good): void
    {
        $body = [
            'id'           => $good->id(),
            'prefix'       => $good->getPrefix(),
            'name'         => $good->getName(),
            'short_info'   => $good->getShortInfo(),
            'description'  => $good->getDescription(),
            'manufacturer' => $good->getManufacturer()?->getName(),
        ];

        try {
            $this->client->single(self::INDEX, $body);
        } catch (\Exception $e) {
            throw new \Exception('Ошибка при попытке добавить товар в ES: ' . $e->getMessage());
        }

        Log::info('Good ' . $good->id() . ' успешно добавлен в ES', $body);

        event(new ClearCacheEvent(CatalogAPICache::class, 'clearCache'));
    }

    public function addBulkGoods(array $goods): void
    {
        $body = [];
        foreach ($goods as $good) {
            $body[] = [
                'id'           => $good->id(),
                'prefix'       => $good->getPrefix(),
                'name'         => $good->getName(),
                'short_info'   => $good->getShortInfo(),
                'description'  => $good->getDescription(),
                'manufacturer' => $good->getManufacturer()?->getName(),
            ];
        }

        $this->client->bulk(self::INDEX, $body);
    }

    public function getByGoodId(int $id): array
    {
        return $this->client->getById(self::INDEX, $id);
    }

    public function searchGoods(string $query, int $limit): array
    {
        return $this->client->search(query: $query, index: self::INDEX, limit: $limit);
    }

    public function clearByIndex(): void
    {
        $this->client->clearByIndex(self::INDEX);
    }
}
