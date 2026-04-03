<?php

declare(strict_types=1);

namespace Tests\Feature\API\Auth;

use App\Notifications\System\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\API\ApiTestCase;

final class ResetPasswordTest extends ApiTestCase
{
    private const string RESET_PASSWORD_ENDPOINT_URL = '/reset-password';

    public function testResetPasswordSendsNotificationAndCreatesTokenForExistingUser(): void
    {
        Notification::fake();

        $user = $this->createUserWithPassword();

        $response = $this->request(
            'POST',
            self::RESET_PASSWORD_ENDPOINT_URL,
            ['email' => $user->email],
            $this->withLocale()
        );

        $this->assertSuccess($response);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function testResetPasswordReturnsSuccessForUnknownEmailWithoutCreatingToken(): void
    {
        Notification::fake();

        $email = 'missing_' . uniqid() . '@example.com';

        $response = $this->request(
            'POST',
            self::RESET_PASSWORD_ENDPOINT_URL,
            ['email' => $email],
            $this->withLocale()
        );

        $this->assertSuccess($response);

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $email,
        ]);
        Notification::assertNothingSent();
    }

    public function testResetPasswordFailsWhenEmailMissing(): void
    {
        $response = $this->request('POST', self::RESET_PASSWORD_ENDPOINT_URL, [], $this->withLocale());

        $response->assertStatus(422)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                    'errors' => [
                        'email',
                    ],
                ],
            ]);
    }

    public function testResetPasswordFailsWhenLocaleHeaderMissing(): void
    {
        $user = $this->createUserWithPassword();

        $response = $this->request('POST', self::RESET_PASSWORD_ENDPOINT_URL, [
            'email' => $user->email,
        ]);

        $response->assertStatus(400)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                ],
            ]);
    }
}

