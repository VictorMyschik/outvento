<?php

declare(strict_types=1);

namespace Tests\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

final class HTTPClientTrait
{
    public function doPost(string $url, array $args, array $headers = []): array
    {
        $client = new Client();

        if (isset($this->authToken)) {
            $headers['Authorization'] = 'Bearer ' . $this->authToken;
        }

        $headers = array_merge($headers, [
            'Content-Type' => 'application/json',
        ]);

        $options = [
            'headers' => $headers,
            'body'    => json_encode($args),
        ];

        try {
            $response = $client->post($url, $options);
        } catch (ClientException  $e) {
            $response = $e->getResponse();
        }

        return [$response->getStatusCode(), json_decode($response->getBody()->getContents(), true)];
    }
}
