<?php

declare(strict_types=1);

namespace Tests\Feature\API\Auth;

use Tests\Feature\API\ApiTestCase;

final class RefreshTokenTest extends ApiTestCase
{

    public function testRefreshReturnsNewTokenPair(): void
    {
        $tokens = $this->issueTokens();

        $response = $this->guestRequest('POST', self::REFRESH_ENDPOINT_URL, [
            'refreshToken' => $tokens['refreshToken'],
        ]);

        $this->assertSuccess($response);

        $response->assertJsonStructure([
            'content' => [
                'accessToken',
                'refreshToken',
                'tokenType',
                'expiresIn',
            ],
        ]);

        $this->assertNotSame($tokens['accessToken'], $response->json('content.accessToken'));
        $this->assertNotSame($tokens['refreshToken'], $response->json('content.refreshToken'));
        $response->assertJsonPath('content.tokenType', 'Bearer');
    }

    public function testRefreshInvalidatesOldRefreshToken(): void
    {
        $tokens = $this->issueTokens();

        $this->guestRequest('POST', self::REFRESH_ENDPOINT_URL, [
            'refreshToken' => $tokens['refreshToken'],
        ])->assertOk();

        $oldRefreshResponse = $this->guestRequest('POST', self::REFRESH_ENDPOINT_URL, [
            'refreshToken' => $tokens['refreshToken'],
        ]);

        $oldRefreshResponse->assertStatus(401)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                ],
            ]);
    }

    public function testRefreshInvalidatesOldAccessTokenAndKeepsNewAccessTokenWorking(): void
    {
        $tokens = $this->issueTokens();

        $refreshResponse = $this->guestRequest('POST', self::REFRESH_ENDPOINT_URL, [
            'refreshToken' => $tokens['refreshToken'],
        ]);

        $this->assertSuccess($refreshResponse);

        $newAccessToken = $refreshResponse->json('content.accessToken');

        $oldAccessResponse = $this->request('GET', self::PROFILE_ENDPOINT_URL, [], $this->bearerHeaders($tokens['accessToken']));

        $oldAccessResponse->assertStatus(401)
            ->assertJson(['status' => 'error']);

        $newAccessResponse = $this->request('GET', self::PROFILE_ENDPOINT_URL, [], $this->bearerHeaders($newAccessToken));

        $this->assertSuccess($newAccessResponse);

        $newAccessResponse->assertJsonStructure([
            'content' => [
                'email',
            ],
        ]);
    }

    public function testRefreshFailsWhenTokenMissing(): void
    {
        $response = $this->guestRequest('POST', self::REFRESH_ENDPOINT_URL, []);

        $response->assertStatus(422)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                    'errors' => [
                        'refreshToken',
                    ],
                ],
            ]);
    }

    public function testRefreshFailsWhenTokenIsNotString(): void
    {
        $response = $this->guestRequest('POST', self::REFRESH_ENDPOINT_URL, [
            'refreshToken' => ['invalid'],
        ]);

        $response->assertStatus(422)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                    'errors' => [
                        'refreshToken',
                    ],
                ],
            ]);
    }

    public function testRefreshFailsWithUnknownToken(): void
    {
        $response = $this->guestRequest('POST', self::REFRESH_ENDPOINT_URL, [
            'refreshToken' => '1|invalid-refresh-token-value',
        ]);

        $response->assertStatus(401)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                ],
            ]);
    }
}