<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\User\GoogleApiInterface;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $client = $this->app->make(GoogleApiInterface::class);
        $r = $client->getTimezoneByCoordinates(
            lat: 40.7128,
            lng: -74.0060
        );
    }

}
