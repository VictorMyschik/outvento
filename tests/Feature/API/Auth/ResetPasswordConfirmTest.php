<?php
declare(strict_types=1);

namespace Tests\Feature\API\Auth;

use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Feature\API\ApiTestCase;

final class ResetPasswordConfirmTest extends ApiTestCase
{
    private const string RESET_PASSWORD_CONFIRM_ENDPOINT_URL = '/reset-password/change';

    public function testResetPasswordConfirmChangesPasswordAndDeletesToken(): void
    {
        $user = $this->createUserWithPassword('OldPassword123!');
        $token = Str::lower(Str::random(60));

        PasswordResetToken::query()->create([
            'email' => $user->email,
            'token' => $token,
        ]);
        $response = $this->guestRequest('POST', self::RESET_PASSWORD_CONFIRM_ENDPOINT_URL, [
            'token'    => $token,
            'password' => 'NewPassword123!',
        ]);

        $this->assertSuccess($response);
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
            'token' => $token,
        ]);

        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));

        $this->request('POST', self::LOGIN_ENDPOINT_URL, [
            'login'    => $user->email,
            'password' => 'OldPassword123!',
        ], $this->withLocale())->assertStatus(401);

        $this->assertSuccess($this->request('POST', self::LOGIN_ENDPOINT_URL, [
            'login'    => $user->email,
            'password' => 'NewPassword123!',
        ], $this->withLocale()));
    }

    public function testResetPasswordConfirmFailsWithInvalidToken(): void
    {
        $response = $this->guestRequest('POST', self::RESET_PASSWORD_CONFIRM_ENDPOINT_URL, [
            'token'    => Str::lower(Str::random(60)),
            'password' => 'NewPassword123!',
        ]);

        $response->assertStatus(404)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                ],
            ]);
    }

    public function testResetPasswordConfirmFailsWithWeakPassword(): void
    {
        $response = $this->guestRequest('POST', self::RESET_PASSWORD_CONFIRM_ENDPOINT_URL, [
            'token'    => Str::lower(Str::random(60)),
            'password' => '123',
        ]);

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
}
