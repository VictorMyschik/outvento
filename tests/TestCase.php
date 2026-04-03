<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    protected function guestJson(string $method, string $uri, array $data = [], array $headers = []): TestResponse
    {
        auth()->forgetGuards();

        return $this->json($method, $uri, $data, $headers);
    }
}
