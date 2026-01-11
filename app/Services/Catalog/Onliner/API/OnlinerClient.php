<?php

declare(strict_types=1);

namespace App\Services\Catalog\Onliner\API;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\StreamInterface;

final readonly class OnlinerClient
{
    public function __construct(private Client $client) {}

    public function doPost(string $url, array $options = []): StreamInterface
    {
        try {
            $response = $this->client->post($url, $options);
        } catch (ClientException $e) {
            $response = $e->getResponse();
        }

        return $response->getBody();
    }

    public function doGet(string $url, array $options = []): StreamInterface
    {
        try {
            $response = $this->client->get($url, $options);
        } catch (ClientException $e) {
            $response = $e->getResponse();
        }

        return $response->getBody();
    }
}
