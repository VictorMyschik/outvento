<?php
declare(strict_types=1);

namespace Tests\Feature\API\Auth;

use App\Models\PasswordResetToken;
use App\Services\User\AuthService;
use Illuminate\Support\Str;
use Tests\Feature\API\ApiTestCase;

final class CheckActualResetPasswordTokenTest extends ApiTestCase
{
    public function testCheckActualResetPasswordTokenReturnsNoContentForValidToken(): void
    {
        $token = Str::lower(Str::random(AuthService::TOKEN_LENGTH));

        PasswordResetToken::query()->create([
            'email' => 'user@example.com',
            'token' => $token,
        ]);

        $response = $this->guestRequest('POST', "/reset-password/{$token}/check");

        $response->assertStatus(204);
    }

    public function testCheckActualResetPasswordTokenFailsForInvalidTokenFormat(): void
    {
        $response = $this->guestRequest('POST', '/reset-password/invalid-token/check');

        $response->assertStatus(403)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                ],
            ]);
    }

    public function testCheckActualResetPasswordTokenFailsForExpiredToken(): void
    {
        $token = Str::lower(Str::random(AuthService::TOKEN_LENGTH));

        PasswordResetToken::query()->create([
            'email' => 'user@example.com',
            'token' => $token,
        ]);

        PasswordResetToken::query()->where('email', 'user@example.com')->update([
            'created_at' => now()->subMinutes(21),
        ]);

        $response = $this->guestRequest('POST', "/reset-password/{$token}/check");

        $response->assertStatus(403)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                ],
            ]);
    }
}
