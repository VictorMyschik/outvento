<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Notifications\NewsNotification;
use App\Repositories\Notifications\NotificationRepository;
use App\Services\Newsletter\NewsletterDispatchService;
use App\Services\Notifications\Enum\EventType;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $service = app(NewsletterDispatchService::class);
        $service->runDispatch();
    }
}
