<?php

declare(strict_types=1);

namespace Tests\Feature\API\Auth;

use App\Notifications\System\VerifyRegistrationCode;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\SystemEvent;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\API\ApiTestCase;

final class VerifyResendTest extends ApiTestCase
{
    private const string VERIFY_RESEND_ENDPOINT_URL = '/user/verify/resend';

    public function testVerifyResendRequiresAuthentication(): void
    {
        $response = $this->request('POST', self::VERIFY_RESEND_ENDPOINT_URL);

        $response->assertStatus(401)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                ],
            ]);
    }

    public function testVerifyResendCreatesNotificationCodeAndSendsNotification(): void
    {
        Notification::fake();

        $password = 'Password123!';
        $user = $this->createUserWithPassword($password, [
            'email_verified_at' => null,
        ]);
        $tokens = $this->issueTokens($user, $password);

        $response = $this->request('POST', self::VERIFY_RESEND_ENDPOINT_URL, [], $this->bearerHeaders($tokens['accessToken']));

        $response->assertStatus(204);

        $this->assertDatabaseHas('notification_codes', [
            'user_id' => $user->id,
            'type'    => SystemEvent::RegistrationConfirmation->value,
            'channel' => NotificationChannel::Email->value,
            'address' => $user->email,
        ]);

        Notification::assertSentTo($user, VerifyRegistrationCode::class);
    }

    public function testVerifyResendFailsForAlreadyVerifiedUser(): void
    {
        $password = 'Password123!';
        $user = $this->createUserWithPassword($password);
        $tokens = $this->issueTokens($user, $password);

        $response = $this->request('POST', self::VERIFY_RESEND_ENDPOINT_URL, [], $this->bearerHeaders($tokens['accessToken']));

        $response->assertStatus(422)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                ],
            ]);
    }
}

