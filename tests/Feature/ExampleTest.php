<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Helpers\TestHelper;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        for ($i = 0; $i < 100; $i++) {
            TestHelper::createNewUser();
        }

    }
}
