<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use App\Models\User;
use App\Models\UserInfo\SocialAccount;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Laravel\Socialite\Facades\Socialite;
use Mockery;

final class SocialAuthControllerTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.frontend_url' => 'https://front.test']);
    }

    public function testRedirectReturnsProviderRedirectResponse(): void
    {
        $driver = Mockery::mock();
        $driver->shouldReceive('stateless')->once()->andReturnSelf();
        $driver->shouldReceive('redirect')->once()->andReturn(redirect('https://oauth.test/redirect'));

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($driver);

        $response = $this->request('GET', '/auth/social/google/redirect');

        $response->assertRedirect('https://oauth.test/redirect');
    }

    public function testCallbackUsesExistingSocialAccountUser(): void
    {
        $user = $this->createUserWithPassword();

        DB::table('social_accounts')->insert([
            'user_id'     => $user->id,
            'provider'    => 'google',
            'provider_id' => 'google-uid-1',
            'created_at'  => now(),
        ]);

        $socialUser = Mockery::mock(SocialiteUserContract::class);
        $socialUser->shouldReceive('getId')->andReturn('google-uid-1');
        $socialUser->shouldReceive('getEmail')->andReturn('different@example.com');
        $socialUser->shouldReceive('getName')->andReturn('Existing Social User');

        $driver = Mockery::mock();
        $driver->shouldReceive('stateless')->once()->andReturnSelf();
        $driver->shouldReceive('user')->once()->andReturn($socialUser);

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($driver);

        $response = $this->request('GET', '/auth/social/google/callback');

        $location = $response->headers->get('Location');
        $response->assertRedirect();
        $this->assertStringStartsWith('https://front.test/auth/success?token=', (string)$location);

        $token = $this->extractTokenFromRedirect($location);
        $tokenModel = PersonalAccessToken::findToken($token);

        $this->assertNotNull($tokenModel);
        $this->assertSame($user->id, $tokenModel->tokenable_id);
        $this->assertSame(1, User::query()->count());
        $this->assertSame(1, SocialAccount::query()->count());
    }

    public function testCallbackLinksSocialAccountToExistingUserByEmail(): void
    {
        $user = $this->createUserWithPassword(attributes: [
            'email' => 'exists@example.com',
            'name'  => 'Existing User',
        ]);

        $socialUser = Mockery::mock(SocialiteUserContract::class);
        $socialUser->shouldReceive('getId')->andReturn('google-uid-2');
        $socialUser->shouldReceive('getEmail')->andReturn('exists@example.com');
        $socialUser->shouldReceive('getName')->andReturn('Any Name');

        $driver = Mockery::mock();
        $driver->shouldReceive('stateless')->once()->andReturnSelf();
        $driver->shouldReceive('user')->once()->andReturn($socialUser);

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($driver);

        $response = $this->request('GET', '/auth/social/google/callback');

        $location = $response->headers->get('Location');
        $response->assertRedirect();

        $this->assertDatabaseHas('social_accounts', [
            'user_id'     => $user->id,
            'provider'    => 'google',
            'provider_id' => 'google-uid-2',
        ]);
        $this->assertSame(1, User::query()->count());

        $token = $this->extractTokenFromRedirect($location);
        $tokenModel = PersonalAccessToken::findToken($token);
        $this->assertNotNull($tokenModel);
        $this->assertSame($user->id, $tokenModel->tokenable_id);
    }

    public function testCallbackCreatesNewUserAndSocialAccountWhenUserNotFound(): void
    {
        $socialUser = Mockery::mock(SocialiteUserContract::class);
        $socialUser->shouldReceive('getId')->andReturn('google-uid-3');
        $socialUser->shouldReceive('getEmail')->andReturn('new@example.com');
        $socialUser->shouldReceive('getName')->andReturnNull();

        $driver = Mockery::mock();
        $driver->shouldReceive('stateless')->once()->andReturnSelf();
        $driver->shouldReceive('user')->once()->andReturn($socialUser);

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($driver);

        $response = $this->request('GET', '/auth/social/google/callback');
        $location = $response->headers->get('Location');

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'name'  => 'User',
        ]);
        $this->assertDatabaseHas('social_accounts', [
            'provider'    => 'google',
            'provider_id' => 'google-uid-3',
        ]);

        $user = User::query()->where('email', 'new@example.com')->firstOrFail();
        $this->assertNotNull($user->email_verified_at);
        $this->assertNotEmpty($user->subscription_token);

        $token = $this->extractTokenFromRedirect($location);
        $tokenModel = PersonalAccessToken::findToken($token);
        $this->assertNotNull($tokenModel);
        $this->assertSame($user->id, $tokenModel->tokenable_id);
    }

    public function testCallbackReturnsNotFoundForUnsupportedProvider(): void
    {
        $response = $this->request('GET', '/auth/social/github/callback');

        $response->assertStatus(404);
        $this->assertSame(0, User::query()->count());
        $this->assertSame(0, SocialAccount::query()->count());
        $this->assertSame(0, PersonalAccessToken::query()->count());
    }

    private function extractTokenFromRedirect(?string $location): string
    {
        $this->assertNotNull($location);

        $query = parse_url($location, PHP_URL_QUERY) ?: '';
        parse_str($query, $params);

        $token = $params['token'] ?? null;
        $this->assertIsString($token);
        $this->assertNotEmpty($token);

        return $token;
    }
}

