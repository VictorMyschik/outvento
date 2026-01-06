<?php

declare(strict_types=1);

namespace App\Services\Elasticsearch;


use Elastic\Elasticsearch\Client;

final readonly class ESClient
{
    public function __construct(private Client $client) {}

    public function ping(): bool
    {
        return (bool)$this->client->ping();
    }

    public function getById(string $index, int $id): array
    {
        $response = $this->client->get([
            'index' => $index,
            'id'    => $id,
        ]);

        return $response->asArray();
    }

    /**
     * Generate index with many articles
     */
    public function bulk(string $index, array $data): array
    {
        $params = [];

        foreach ($data as $item) {
            $params['body'][] = ['index' => ['_index' => $index]];
            $params['body'][] = $item;
        }

        $response = $this->client->bulk($params);

        return $response->asArray();
    }

    public function single(string $index, array $data): array
    {
        $params = [
            'index' => $index,
            'id'    => $data['id'],
            'body'  => $data
        ];

        $response = $this->client->index($params);

        return $response->asArray();
    }

    public function search(string $query, string $index, int $limit = 10): array
    {
        $params = [
            "index" => $index,
            "from"  => 0, "size" => $limit, // Elastic use pagination, get first page
            "body"  => [
                "query" => [
                    "bool" => [
                        "must"   => [
                            [
                                'multi_match' => [
                                    'query' => $query,
                                ],
                            ]
                        ],
                        "filter" => []
                    ]
                ]
            ]
        ];

        $response = $this->client->search($params);

        return $response->asArray();
    }

    public function clearByIndex(string $index): void
    {
        $params = [
            'index'     => $index,
            'body'      => [
                'query' => [
                    'match_all' => new \stdClass()
                ]
            ],
            'conflicts' => 'proceed'
        ];

        $this->client->deleteByQuery($params);
    }
}
