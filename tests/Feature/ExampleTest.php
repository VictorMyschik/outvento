<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Notifications\InternalNotificationService;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $service = app(InternalNotificationService::class);
        $service->send(1, 'Test Title', 'Test Message');
    }

}
