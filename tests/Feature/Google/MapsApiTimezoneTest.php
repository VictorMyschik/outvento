<?php

declare(strict_types=1);

namespace Tests\Feature\Google;

use App\Services\User\GoogleApiInterface;
use Tests\TestCase;

class MapsApiTimezoneTest extends TestCase
{
    public function testGetTomeZone(): void
    {
        $client = $this->app->make(GoogleApiInterface::class);
        $result = $client->getTimezoneByCoordinates(
            lat: 40.7128,
            lng: -74.0060
        );

        self::assertSame('America/New_York', $result);
    }
}