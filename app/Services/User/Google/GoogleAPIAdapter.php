<?php

declare(strict_types=1);

namespace App\Services\User\Google;

use App\Services\User\GoogleApiInterface;

final readonly class GoogleAPIAdapter implements GoogleApiInterface
{
    public function __construct(
        private GoogleClient $client,
        private array        $config
    ) {}

    public function getTimezoneByCoordinates(float $lat, float $lng): string
    {
        $request = [
            'location'  => $lat . ',' . $lng,
            'timestamp' => time(),
            'key'       => $this->config['map_key'],
        ];

        $url = $this->config['services']['timezone']['url'];

        $response = $this->client->getTimezone($url, $request);

        if ($response->status !== 'OK') {
            throw new \RuntimeException('Failed to get timezone from Google API. Status: ' . $response->status);
        }

        return $response->timeZoneId;
    }
}