<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Travel\Travel;
use App\Models\Travel\UIT;
use App\Services\Newsletter\NewsletterDispatchService;
use App\Services\Telegram\Client;
use App\Services\Travel\Enum\UITStatus;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        app(Client::class)->setWebHook();
    }
}
