<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Image\AlbumImageResizer;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $service = app(AlbumImageResizer::class);
        $service->resize(92);
    }
}
