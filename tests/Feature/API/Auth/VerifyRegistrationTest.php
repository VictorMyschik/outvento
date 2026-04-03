<?php

declare(strict_types=1);

namespace Tests\Feature\API\Auth;

use App\Models\NotificationCode;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\SystemEvent;
use Tests\Feature\API\ApiTestCase;

final class VerifyRegistrationTest extends ApiTestCase
{
    private const string VERIFY_REGISTRATION_ENDPOINT_URL = '/user/verify';

    public function testVerifyRegistrationRequiresAuthentication(): void
    {
        $response = $this->request('POST', self::VERIFY_REGISTRATION_ENDPOINT_URL, [
            'code' => '123456',
        ]);

        $response->assertStatus(401)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                ],
            ]);
    }

    public function testVerifyRegistrationMarksEmailAsVerifiedAndDeletesNotificationCode(): void
    {
        $password = 'Password123!';
        $user = $this->createUserWithPassword($password, [
            'email_verified_at' => null,
        ]);

        NotificationCode::query()->create([
            'user_id' => $user->id,
            'code'    => '123456',
            'type'    => SystemEvent::RegistrationConfirmation->value,
            'channel' => NotificationChannel::Email->value,
            'address' => $user->email,
        ]);

        $tokens = $this->issueTokens($user, $password);

        $response = $this->request(
            'POST',
            self::VERIFY_REGISTRATION_ENDPOINT_URL,
            ['code' => '123456'],
            $this->bearerHeaders($tokens['accessToken'])
        );

        $response->assertStatus(204);

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertDatabaseMissing('notification_codes', [
            'user_id' => $user->id,
            'code'    => '123456',
        ]);
    }

    public function testVerifyRegistrationFailsWithUnknownCode(): void
    {
        $password = 'Password123!';
        $user = $this->createUserWithPassword($password, [
            'email_verified_at' => null,
        ]);
        $tokens = $this->issueTokens($user, $password);

        $response = $this->request(
            'POST',
            self::VERIFY_REGISTRATION_ENDPOINT_URL,
            ['code' => '123456'],
            $this->bearerHeaders($tokens['accessToken'])
        );

        $response->assertStatus(404)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                ],
            ]);
    }

    public function testVerifyRegistrationFailsWithInvalidCodeFormat(): void
    {
        $password = 'Password123!';
        $user = $this->createUserWithPassword($password, [
            'email_verified_at' => null,
        ]);
        $tokens = $this->issueTokens($user, $password);

        $response = $this->request(
            'POST',
            self::VERIFY_REGISTRATION_ENDPOINT_URL,
            ['code' => '12345'],
            $this->bearerHeaders($tokens['accessToken'])
        );

        $response->assertStatus(422)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                    'errors' => [
                        'code',
                    ],
                ],
            ]);
    }
}

