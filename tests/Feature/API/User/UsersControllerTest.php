<?php
declare(strict_types=1);

namespace Tests\Feature\API\User;

use App\Notifications\System\VerifyRegistrationCode;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\SystemEvent;
use App\Services\User\Enum\CommunicationType;
use App\Services\User\Enum\Visibility;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\API\ApiTestCase;

final class UsersControllerTest extends ApiTestCase
{
    private const string PROFILE_FULL_ENDPOINT_URL = '/user/full';
    private const string UPDATE_PROFILE_ENDPOINT_URL = '/user/profile/edit';
    private const string REMOVE_AVATAR_ENDPOINT_URL = '/user/avatar';
    private const string COMMUNICATIONS_ENDPOINT_URL = '/user/communications';
    private const string CREATE_COMMUNICATION_ENDPOINT_URL = '/user/communications';

    public function testProfileRequiresAuthentication(): void
    {
        $response = $this->request('GET', self::PROFILE_ENDPOINT_URL);
        $response->assertStatus(401)
            ->assertJson(['status' => 'error']);
    }

    public function testProfileReturnsCurrentUserData(): void
    {
        $user = $this->createUserWithPassword(attributes: [
            'email'             => 'profile@example.com',
            'name'              => 'profile_user',
            'email_verified_at' => now(),
        ]);
        $tokens = $this->issueTokens($user);
        $response = $this->request('GET', self::PROFILE_ENDPOINT_URL, [], $this->bearerHeaders($tokens['accessToken']));
        $this->assertSuccess($response);
        $response->assertJsonPath('content.email', 'profile@example.com')
            ->assertJsonPath('content.name', 'profile_user')
            ->assertJsonPath('content.isVerified', true)
            ->assertJsonStructure([
                'content' => [
                    'id',
                    'name',
                    'email',
                    'avatar',
                    'defaultLanguage',
                    'isVerified',
                    'telegram',
                    'firstName',
                    'lastName',
                    'gender',
                    'birthday',
                    'about',
                    'updatedAt',
                ],
            ]);
    }

    public function testProfileFullReturnsExtendedUserData(): void
    {
        $user = $this->createUserWithPassword(attributes: [
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'gender'     => 1,
            'birthday'   => '1990-01-01',
            'about'      => 'About me',
        ]);
        $tokens = $this->issueTokens($user);
        $response = $this->request('GET', self::PROFILE_FULL_ENDPOINT_URL, [], $this->bearerHeaders($tokens['accessToken']));
        $this->assertSuccess($response);
        $response->assertJsonPath('content.firstName', 'John')
            ->assertJsonPath('content.lastName', 'Doe')
            ->assertJsonPath('content.birthday', '1990-01-01')
            ->assertJsonPath('content.about', 'About me');
    }

    public function testUpdateProfileUpdatesFieldsAndResetsVerificationWhenEmailChanged(): void
    {
        Notification::fake();
        $user = $this->createUserWithPassword(attributes: [
            'email'             => 'old@example.com',
            'email_verified_at' => now(),
        ]);
        $tokens = $this->issueTokens($user);
        $response = $this->request('POST', self::UPDATE_PROFILE_ENDPOINT_URL, [
            'name'                => 'updated_name',
            'email'               => 'new@example.com',
            'first_name'          => 'Jane',
            'last_name'           => 'Smith',
            'gender'              => 2,
            'birthday'            => '1991-02-03',
            'about'               => 'Updated about',
            'visibility'          => 1,
            'relationship_status' => 2,
        ], $this->bearerHeaders($tokens['accessToken']));

        $this->assertSuccess($response);

        $user = $user->fresh();

        $this->assertSame('updated_name', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
        $this->assertSame('Jane', $user->first_name);
        $this->assertSame('Smith', $user->last_name);
        $this->assertSame(2, $user->gender);
        $this->assertSame('Updated about', $user->about);

        $this->assertDatabaseHas('notification_codes', [
            'user_id' => $user->id,
            'type'    => SystemEvent::RegistrationConfirmation->value,
            'channel' => NotificationChannel::Email->value,
            'address' => 'new@example.com',
        ]);

        Notification::assertSentTo($user, VerifyRegistrationCode::class);
    }

    public function testUpdateProfileFailsWithInvalidBirthday(): void
    {
        $user = $this->createUserWithPassword();
        $tokens = $this->issueTokens($user);

        $response = $this->request('POST', self::UPDATE_PROFILE_ENDPOINT_URL, [
            'birthday' => now()->addDay()->toDateString(),
        ], $this->bearerHeaders($tokens['accessToken']));

        $response->assertStatus(422)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                    'errors' => ['birthday'],
                ],
            ]);
    }

    public function testRemoveAvatarDeletesStoredFileAndClearsAvatarField(): void
    {
        $user = $this->createUserWithPassword();
        $avatarPath = $user->id . '/avatar/avatar.jpg';
        Storage::disk('users')->put($avatarPath, 'avatar-content');
        $user->forceFill(['avatar' => $avatarPath])->save();
        $tokens = $this->issueTokens($user);
        $response = $this->request('DELETE', self::REMOVE_AVATAR_ENDPOINT_URL, [], $this->bearerHeaders($tokens['accessToken']));
        $response->assertStatus(204);
        $this->assertNull($user->fresh()->avatar);
        $this->assertFalse(Storage::disk('users')->exists($avatarPath));
    }

    public function testGetCommunicationsReturnsOnlyCurrentUserCommunications(): void
    {
        $user = $this->createUserWithPassword();
        $otherUser = $this->createUserWithPassword();
        $tokens = $this->issueTokens($user);
        DB::table('communications')->insert([
            [
                'user_id'             => $user->id,
                'type'                => CommunicationType::Telegram->value,
                'address'             => '@my_handle',
                'description'         => 'My telegram',
                'visibility'          => Visibility::Public->value,
                'verification_status' => 0,
                'created_at'          => now(),
            ],
            [
                'user_id'             => $otherUser->id,
                'type'                => CommunicationType::Telegram->value,
                'address'             => '@other_handle',
                'description'         => 'Other telegram',
                'visibility'          => Visibility::Private->value,
                'verification_status' => 0,
                'created_at'          => now(),
            ],
        ]);
        $response = $this->request('GET', self::COMMUNICATIONS_ENDPOINT_URL, [], $this->bearerHeaders($tokens['accessToken']));

        $this->assertSuccess($response);
        $this->assertCount(1, $response->json('content'));
        $response->assertJsonPath('content.0.address', '@my_handle');
    }

    public function testCreateCommunicationCreatesTelegramCommunication(): void
    {
        $user = $this->createUserWithPassword();
        $tokens = $this->issueTokens($user);

        $response = $this->request('POST', self::CREATE_COMMUNICATION_ENDPOINT_URL, [
            'type'        => CommunicationType::Telegram->value,
            'address'     => '@new_handle',
            'description' => 'Telegram contact',
            'visibility'  => Visibility::Public->value,
        ], $this->bearerHeaders($tokens['accessToken']));

        $response->assertStatus(201);

        $this->assertDatabaseHas('communications', [
            'user_id'     => $user->id,
            'type'        => CommunicationType::Telegram->value,
            'address'     => '@new_handle',
            'description' => 'Telegram contact',
            'visibility'  => Visibility::Public->value,
        ]);
    }

    public function testCreateCommunicationFailsWithMissingAddress(): void
    {
        $user = $this->createUserWithPassword();
        $tokens = $this->issueTokens($user);

        $response = $this->request('POST', self::CREATE_COMMUNICATION_ENDPOINT_URL, [
            'type'       => CommunicationType::Telegram->value,
            'visibility' => Visibility::Public->value,
        ], $this->bearerHeaders($tokens['accessToken']));

        $response->assertStatus(422)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                    'errors' => ['address'],
                ],
            ]);
    }

    public function testUpdateCommunicationUpdatesOwnedCommunication(): void
    {
        $user = $this->createUserWithPassword();
        $tokens = $this->issueTokens($user);

        $communicationId = DB::table('communications')->insertGetId([
            'user_id'             => $user->id,
            'type'                => CommunicationType::Telegram->value,
            'address'             => '@old_handle',
            'description'         => 'Old desc',
            'visibility'          => Visibility::Private->value,
            'verification_status' => 0,
            'created_at'          => now(),
        ]);

        $response = $this->request('PUT', self::COMMUNICATIONS_ENDPOINT_URL . '/' . $communicationId, [
            'type'        => CommunicationType::Telegram->value,
            'address'     => '@updated_handle',
            'description' => 'Updated desc',
            'visibility'  => Visibility::Public->value,
        ], $this->bearerHeaders($tokens['accessToken']));

        $response->assertStatus(204);

        $this->assertDatabaseHas('communications', [
            'id'          => $communicationId,
            'user_id'     => $user->id,
            'address'     => '@updated_handle',
            'description' => 'Updated desc',
            'visibility'  => Visibility::Public->value,
        ]);
    }

    public function testDeleteCommunicationDeletesOwnedCommunication(): void
    {
        $user = $this->createUserWithPassword();
        $tokens = $this->issueTokens($user);

        $communicationId = DB::table('communications')->insertGetId([
            'user_id'             => $user->id,
            'type'                => CommunicationType::Telegram->value,
            'address'             => '@delete_me',
            'description'         => 'Delete me',
            'visibility'          => Visibility::Private->value,
            'verification_status' => 0,
            'created_at'          => now(),
        ]);

        $response = $this->request('DELETE', self::COMMUNICATIONS_ENDPOINT_URL . '/' . $communicationId, [], $this->bearerHeaders($tokens['accessToken']));

        $response->assertStatus(204);

        $this->assertDatabaseMissing('communications', [
            'id' => $communicationId,
        ]);
    }

    public function testGetUserAvatarReturnsDefaultAvatarWhenUserHasNoAvatar(): void
    {
        Storage::disk('public')->put('images/users/avatar.png', 'default-avatar');
        $user = $this->createUserWithPassword(attributes: ['avatar' => null]);
        $response = $this->request('GET', '/user/' . $user->id . '/avatar');
        $response->assertOk();
    }

    public function testGetUserAvatarReturnsUserAvatarFileWhenAvatarExists(): void
    {
        $user = $this->createUserWithPassword();
        $avatarPath = $user->id . '/avatar/avatar.png';
        Storage::disk('users')->put($avatarPath, 'custom-avatar');
        $user->forceFill(['avatar' => $avatarPath])->save();
        $response = $this->request('GET', '/user/' . $user->id . '/avatar');
        $response->assertOk();
    }
}
