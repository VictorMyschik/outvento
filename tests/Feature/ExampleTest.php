<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $client = $this->app->make(\App\Services\Telegram\Client::class);
        $client->sendMessage(488545536, 'Hello! I am a bot! Send me a link of OLX site to the offer.');
    }

}
