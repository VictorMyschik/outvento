<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use App\Models\User;
use App\Services\System\Enum\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected const string LOGIN_ENDPOINT_URL = '/login';
    protected const string PROFILE_ENDPOINT_URL = '/user';
    protected const string REFRESH_ENDPOINT_URL = '/refresh';
    protected const string LOGOUT_ENDPOINT_URL = '/logout';
    protected const string LOGOUT_ALL_ENDPOINT_URL = '/logout-all';

    protected $defaultHeaders = [
        'Accept' => 'application/json',
    ];

    protected function request(string $method, string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json(
            $method,
            '/api/v1' . $uri,
            $data,
            array_merge($this->defaultHeaders, $headers)
        );
    }

    protected function guestRequest(string $method, string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->guestJson(
            $method,
            '/api/v1' . $uri,
            $data,
            array_merge($this->defaultHeaders, $headers)
        );
    }

    protected function actingAsUser(?User $user = null): User
    {
        $user ??= User::factory()->create();

        Sanctum::actingAs($user);

        return $user;
    }

    protected function createUserWithPassword(string $password = 'Password123!', array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'password' => bcrypt($password),
        ], $attributes));
    }

    protected function issueTokens(?User $user = null, string $password = 'Password123!', array $payload = []): array
    {
        $user ??= $this->createUserWithPassword($password);

        $response = $this->request('POST', self::LOGIN_ENDPOINT_URL, array_merge([
            'login'    => $user->email,
            'password' => $password,
        ], $payload), $this->withLocale());

        $response->assertOk();

        return $response->json('content');
    }

    protected function bearerHeaders(string $accessToken): array
    {
        return [
            'Authorization' => 'Bearer ' . $accessToken,
        ];
    }

    protected function withLocale(Language $locale = Language::EN): array
    {
        return [
            'X-Locale' => $locale->getCode(),
        ];
    }

    protected function assertSuccess(TestResponse $response): void
    {
        $response->assertOk()
            ->assertJson([
                'status' => 'ok',
            ]);
    }
}