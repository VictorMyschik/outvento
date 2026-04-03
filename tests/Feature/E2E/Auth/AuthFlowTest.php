<?php

declare(strict_types=1);

namespace Tests\Feature\E2E\Auth;

use Illuminate\Support\Str;
use Tests\Helpers\EmailGenerator;
use Tests\Helpers\RawDataHelper;
use Tests\TestCase;

final class AuthFlowTest extends TestCase
{
    public function testFullAuthFlow(): void
    {
        $name = RawDataHelper::randomName();
        $email = EmailGenerator::generateEmail($name . '_' . uniqid());
        $password = Str::password(12);

        // 1. Register
        $registerResponse = $this->json('POST', route('api.v1.register'), [
            'name'                  => $name,
            'email'                 => $email,
            'password'              => $password,
            'password_confirmation' => $password,
        ], [
            'X-Locale' => 'en',
        ]);

        $registerResponse->assertCreated()
            ->assertJson(['status' => 'ok'])
            ->assertJsonStructure([
                'content' => [
                    'accessToken',
                    'refreshToken',
                    'tokenType',
                    'expiresIn',
                ],
            ]);

        $accessToken = $registerResponse->json('content.accessToken');
        $refreshToken = $registerResponse->json('content.refreshToken');

        $this->assertNotEmpty($accessToken);
        $this->assertNotEmpty($refreshToken);

        // 2. Проверка access token (/me)
        $meResponse = $this->json('GET', route('api.v1.user.profile'), [], [
            'Authorization' => 'Bearer ' . $accessToken,
        ]);

        $meResponse->assertOk()
            ->assertJson(['status' => 'ok'])
            ->assertJsonStructure([
                'content' => [
                    'email',
                ],
            ]);

        $this->assertEquals($email, $meResponse->json('content.email'));

        // 3. Refresh token
        $refreshResponse = $this->guestJson('POST', route('api.v1.refresh'), [
            'refreshToken' => $refreshToken,
        ]);

        $refreshResponse->assertOk()
            ->assertJson(['status' => 'ok'])
            ->assertJsonStructure([
                'content' => [
                    'accessToken',
                    'refreshToken',
                ],
            ]);

        $newAccessToken = $refreshResponse->json('content.accessToken');
        $newRefreshToken = $refreshResponse->json('content.refreshToken');

        $this->assertNotEmpty($newAccessToken);
        $this->assertNotEmpty($newRefreshToken);

        // Проверяем, что токены обновились
        $this->assertNotEquals($accessToken, $newAccessToken);
        $this->assertNotEquals($refreshToken, $newRefreshToken);

        // 4. Старый refresh token больше не работает (rotation)
        $oldRefreshResponse = $this->guestJson('POST', route('api.v1.refresh'), [
            'refreshToken' => $refreshToken,
        ]);

        $oldRefreshResponse->assertStatus(401);

        // 5. Новый access token работает
        $meResponse = $this->json('GET', route('api.v1.user.profile'), [], [
            'Authorization' => 'Bearer ' . $newAccessToken,
        ]);

        $meResponse->assertOk();

        // 6. Logout
        $logoutResponse = $this->json('POST', route('api.v1.logout'), [], [
            'Authorization' => 'Bearer ' . $newAccessToken,
        ]);

        $logoutResponse->assertOk();

        // 7. Проверка, что токен больше не работает
        $meResponse = $this->guestJson('GET', route('api.v1.user.profile'), [], [
            'Authorization' => 'Bearer ' . $newAccessToken,
        ]);

        $meResponse->assertStatus(401);
    }
}