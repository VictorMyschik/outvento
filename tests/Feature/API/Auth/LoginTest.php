<?php

declare(strict_types=1);

namespace Tests\Feature\API\Auth;

use App\Models\User;
use Tests\Feature\API\ApiTestCase;

final class LoginTest extends ApiTestCase
{

    public function testSuccessLoginByEmail(): void
    {
        $password = 'Password123!';
        $user = $this->createUserWithPassword($password);

        $response = $this->request(
            'POST',
            self::LOGIN_ENDPOINT_URL,
            [
                'login'    => $user->email,
                'password' => $password,
            ],
            $this->withLocale()
        );

        $this->assertSuccess($response);

        $response->assertJsonStructure([
            'content' => [
                'accessToken',
                'refreshToken',
                'tokenType',
                'expiresIn',
            ],
        ])
            ->assertJsonPath('content.expiresIn', 60 * 60);
    }

    public function testSuccessLoginByName(): void
    {
        $password = 'Password123!';
        $user = $this->createUserWithPassword($password, [
            'name' => 'login_user_' . uniqid(),
        ]);

        $response = $this->request(
            'POST',
            self::LOGIN_ENDPOINT_URL,
            [
                'login'    => $user->name,
                'password' => $password,
            ],
            $this->withLocale()
        );

        $this->assertSuccess($response);

        $response->assertJsonStructure([
            'content' => [
                'accessToken',
                'refreshToken',
                'tokenType',
                'expiresIn',
            ],
        ]);
    }

    public function testLoginWithRememberIssuesLongerAccessTokenTtl(): void
    {
        $password = 'Password123!';
        $user = $this->createUserWithPassword($password);

        $response = $this->request(
            'POST',
            self::LOGIN_ENDPOINT_URL,
            [
                'login'    => $user->email,
                'password' => $password,
                'remember' => true,
            ],
            $this->withLocale()
        );

        $this->assertSuccess($response);

        $response->assertJsonPath('content.expiresIn', 60 * 60 * 24 * 7);
    }

    public function testLoginFailsWithWrongPassword(): void
    {
        $user = $this->createUserWithPassword();

        $response = $this->request(
            'POST',
            self::LOGIN_ENDPOINT_URL,
            [
                'login'    => $user->email,
                'password' => 'WrongPassword123!',
            ],
            $this->withLocale()
        );

        $response->assertStatus(401)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                ],
            ]);
    }

    public function testLoginFailsWithUnknownUser(): void
    {
        $response = $this->request(
            'POST',
            self::LOGIN_ENDPOINT_URL,
            [
                'login'    => 'missing_' . uniqid() . '@example.com',
                'password' => 'Password123!',
            ],
            $this->withLocale()
        );

        $response->assertStatus(401)
            ->assertJson(['status' => 'error']);
    }

    public function testLoginFailsWhenPasswordMissing(): void
    {
        $user = User::factory()->create();

        $response = $this->request(
            'POST',
            self::LOGIN_ENDPOINT_URL,
            [
                'login' => $user->email,
            ],
            $this->withLocale()
        );

        $response->assertStatus(422)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                    'errors' => [
                        'password',
                    ],
                ],
            ]);
    }

    public function testLoginFailsWhenLoginMissing(): void
    {
        $response = $this->request(
            'POST',
            self::LOGIN_ENDPOINT_URL,
            [
                'password' => 'Password123!',
            ],
            $this->withLocale()
        );

        $response->assertStatus(422)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                    'errors' => [
                        'login',
                    ],
                ],
            ]);
    }

    public function testLoginFailsWhenRememberIsNotBoolean(): void
    {
        $password = 'Password123!';
        $user = $this->createUserWithPassword($password);

        $response = $this->request(
            'POST',
            self::LOGIN_ENDPOINT_URL,
            [
                'login'    => $user->email,
                'password' => $password,
                'remember' => 'invalid',
            ],
            $this->withLocale()
        );

        $response->assertStatus(422)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                    'errors' => [
                        'remember',
                    ],
                ],
            ]);
    }
}