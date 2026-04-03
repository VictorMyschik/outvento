<?php
declare(strict_types=1);

namespace Tests\Feature\API\Auth;

use Illuminate\Support\Facades\Hash;
use Tests\Feature\API\ApiTestCase;

final class ChangePasswordTest extends ApiTestCase
{
    private const string CHANGE_PASSWORD_ENDPOINT_URL = '/user/password';

    public function testChangePasswordRequiresAuthentication(): void
    {
        $response = $this->request('POST', self::CHANGE_PASSWORD_ENDPOINT_URL, [
            'current_password'      => 'Password123!',
            'password'              => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(401)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                ],
            ]);
    }

    public function testChangePasswordUpdatesPasswordWhenCurrentPasswordMatches(): void
    {
        $user = $this->createUserWithPassword('CurrentPassword123!');
        $tokens = $this->issueTokens($user, 'CurrentPassword123!');

        $response = $this->request('POST', self::CHANGE_PASSWORD_ENDPOINT_URL, [
            'current_password'      => 'CurrentPassword123!',
            'password'              => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ], $this->bearerHeaders($tokens['accessToken']));

        $response->assertStatus(204);
        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));

        $this->guestRequest('POST', self::LOGIN_ENDPOINT_URL, [
            'login'    => $user->email,
            'password' => 'CurrentPassword123!',
        ], $this->withLocale())->assertStatus(401);

        $this->assertSuccess($this->guestRequest('POST', self::LOGIN_ENDPOINT_URL, [
            'login'    => $user->email,
            'password' => 'NewPassword123!',
        ], $this->withLocale()));
    }

    public function testChangePasswordFailsWithWrongCurrentPassword(): void
    {
        $user = $this->createUserWithPassword('CurrentPassword123!');
        $tokens = $this->issueTokens($user, 'CurrentPassword123!');

        $response = $this->request('POST', self::CHANGE_PASSWORD_ENDPOINT_URL, [
            'current_password'      => 'WrongPassword123!',
            'password'              => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ], $this->bearerHeaders($tokens['accessToken']));

        $response->assertStatus(422)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                    'errors' => [
                        'current_password',
                    ],
                ],
            ]);
    }

    public function testChangePasswordFailsWhenConfirmationDoesNotMatch(): void
    {
        $user = $this->createUserWithPassword('CurrentPassword123!');
        $tokens = $this->issueTokens($user, 'CurrentPassword123!');

        $response = $this->request('POST', self::CHANGE_PASSWORD_ENDPOINT_URL, [
            'current_password'      => 'CurrentPassword123!',
            'password'              => 'NewPassword123!',
            'password_confirmation' => 'DifferentPassword123!',
        ], $this->bearerHeaders($tokens['accessToken']));

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
