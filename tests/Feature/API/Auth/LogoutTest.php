<?php

declare(strict_types=1);

namespace Tests\Feature\API\Auth;

use Laravel\Sanctum\PersonalAccessToken;
use Tests\Feature\API\ApiTestCase;

final class LogoutTest extends ApiTestCase
{

    public function testLogoutRequiresAuthentication(): void
    {
        $response = $this->request('POST', self::LOGOUT_ENDPOINT_URL);

        $response->assertStatus(401)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                ],
            ]);
    }

    public function testLogoutRevokesCurrentAccessToken(): void
    {
        $tokens = $this->issueTokens();

        $this->assertNotNull(PersonalAccessToken::findToken($tokens['accessToken']));

        $logoutResponse = $this->request('POST', self::LOGOUT_ENDPOINT_URL, [], $this->bearerHeaders($tokens['accessToken']));

        $this->assertSuccess($logoutResponse);

        $this->assertNull(PersonalAccessToken::findToken($tokens['accessToken']));
        $this->assertNotNull(PersonalAccessToken::findToken($tokens['refreshToken']));

        $profileResponse = $this->guestRequest('GET', self::PROFILE_ENDPOINT_URL, [], $this->bearerHeaders($tokens['accessToken']));

        $profileResponse->assertStatus(401)
            ->assertJson(['status' => 'error']);
    }

    public function testLogoutDoesNotRevokeOtherSessionTokens(): void
    {
        $user = $this->createUserWithPassword();

        $firstSessionTokens = $this->issueTokens($user);
        $secondSessionTokens = $this->issueTokens($user);

        $this->request('POST', self::LOGOUT_ENDPOINT_URL, [], $this->bearerHeaders($firstSessionTokens['accessToken']))->assertOk();

        $this->assertNull(PersonalAccessToken::findToken($firstSessionTokens['accessToken']));
        $this->assertNotNull(PersonalAccessToken::findToken($secondSessionTokens['accessToken']));
        $this->assertNotNull(PersonalAccessToken::findToken($secondSessionTokens['refreshToken']));

        $secondSessionProfileResponse = $this->request('GET', self::PROFILE_ENDPOINT_URL, [], $this->bearerHeaders($secondSessionTokens['accessToken']));

        $this->assertSuccess($secondSessionProfileResponse);
    }

    public function testLogoutAllRequiresAuthentication(): void
    {
        $response = $this->request('POST', self::LOGOUT_ALL_ENDPOINT_URL);

        $response->assertStatus(401)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                ],
            ]);
    }

    public function testLogoutAllRevokesAllTokensForCurrentUser(): void
    {
        $user = $this->createUserWithPassword();

        $firstSessionTokens = $this->issueTokens($user);
        $secondSessionTokens = $this->issueTokens($user);

        $logoutAllResponse = $this->request('POST', self::LOGOUT_ALL_ENDPOINT_URL, [], $this->bearerHeaders($firstSessionTokens['accessToken']));

        $this->assertSuccess($logoutAllResponse);

        $this->assertNull(PersonalAccessToken::findToken($firstSessionTokens['accessToken']));
        $this->assertNull(PersonalAccessToken::findToken($firstSessionTokens['refreshToken']));
        $this->assertNull(PersonalAccessToken::findToken($secondSessionTokens['accessToken']));
        $this->assertNull(PersonalAccessToken::findToken($secondSessionTokens['refreshToken']));

        $this->guestRequest('GET', self::PROFILE_ENDPOINT_URL, [], $this->bearerHeaders($secondSessionTokens['accessToken']))->assertStatus(401);

        $this->guestRequest('POST', self::REFRESH_ENDPOINT_URL, [
            'refreshToken' => $secondSessionTokens['refreshToken'],
        ])->assertStatus(401);
    }

    public function testLogoutAllDoesNotRevokeOtherUsersTokens(): void
    {
        $currentUser = $this->createUserWithPassword();
        $otherUser = $this->createUserWithPassword();

        $currentUserTokens = $this->issueTokens($currentUser);
        $otherUserTokens = $this->issueTokens($otherUser);

        $this->request('POST', self::LOGOUT_ALL_ENDPOINT_URL, [], $this->bearerHeaders($currentUserTokens['accessToken']))->assertOk();

        $this->assertNull(PersonalAccessToken::findToken($currentUserTokens['accessToken']));
        $this->assertNull(PersonalAccessToken::findToken($currentUserTokens['refreshToken']));
        $this->assertNotNull(PersonalAccessToken::findToken($otherUserTokens['accessToken']));
        $this->assertNotNull(PersonalAccessToken::findToken($otherUserTokens['refreshToken']));

        $this->guestRequest('GET', self::PROFILE_ENDPOINT_URL, [], $this->bearerHeaders($otherUserTokens['accessToken']))->assertOk();
    }
}

