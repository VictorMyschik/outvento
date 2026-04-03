<?php

declare(strict_types=1);

namespace Tests\Feature\API\Auth;

use App\Models\User;
use App\Services\System\Enum\Language;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\API\ApiTestCase;
use Tests\Helpers\EmailGenerator;
use Tests\Helpers\RawDataHelper;

final class RegisterTest extends ApiTestCase
{
    private const string REGISTER_ENDPOINT_URL = '/register';

    public function testSuccessRegister(): void
    {
        $name = RawDataHelper::randomName();
        $email = EmailGenerator::generateEmail($name);
        $password = 'All1510425!';

        $response = $this->request(
            'POST',
            self::REGISTER_ENDPOINT_URL,
            [
                'name'                  => $name,
                'email'                 => $email,
                'password'              => $password,
                'password_confirmation' => $password,
            ],
            $this->withLocale()
        );

        $response->assertCreated()
            ->assertJson([
                'status' => 'ok',
            ])
            ->assertJsonStructure([
                'content' => [
                    'accessToken',
                    'refreshToken',
                    'tokenType',
                    'expiresIn',
                ]
            ]);

        $body = $response->decodeResponseJson();

        $this->assertNotEmpty($body['content']['accessToken']);
        $this->assertIsString($body['content']['accessToken']);

        $this->assertDatabaseHas('users', [
            'email' => $email,
        ]);

        $user = User::query()->where('email', $email)->firstOrFail();
        $this->assertTrue(Hash::check($password, $user->password));
    }

    public function testRegisterFailsWithWeakPassword(): void
    {
        $response = $this->request(
            'POST',
            self::REGISTER_ENDPOINT_URL,
            [
                'name'                  => 'Test',
                'email'                 => 'test@example.com',
                'password'              => '123',
                'password_confirmation' => '123',
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

        $body = $response->json();

        $this->assertNotEmpty($body['content']['errors']['password']);
        $this->assertIsArray($body['content']['errors']['password']);

        $this->assertStringContainsString(
            'The password must be at least 8 characters long.',
            $body['content']['errors']['password'][0]
        );
    }

    public static function passwordProvider(): array
    {
        return [
            'no_uppercase' => ['password123!'],
            'no_lowercase' => ['PASSWORD123!'],
            'no_number'    => ['Password!'],
            'no_special'   => ['Password123'],
            'too_short'    => ['P1!a'],
        ];
    }

    /**
     * @dataProvider passwordProvider
     */
    public function testRegisterFailsWithInvalidPassword(string $password): void
    {
        $response = $this->request(
            'POST',
            self::REGISTER_ENDPOINT_URL,
            [
                'name'                  => 'Test',
                'email'                 => 'test_' . uniqid() . '@example.com',
                'password'              => $password,
                'password_confirmation' => $password,
            ],
            $this->withLocale()
        );

        $response->assertStatus(422);
    }

    public function testRegisterFailsIfEmailAlreadyExists(): void
    {
        $email = 'test@example.com';

        User::factory()->create([
            'email' => $email,
        ]);

        $response = $this->request(
            'POST',
            self::REGISTER_ENDPOINT_URL,
            [
                'name'                  => 'Test',
                'email'                 => $email,
                'password'              => 'Password123!',
                'password_confirmation' => 'Password123!',
            ],
            $this->withLocale()
        );

        $response->assertStatus(422)
            ->assertJsonStructure([
                'content' => [
                    'message',
                    'errors' => [
                        'email',
                    ],
                ],
            ]);
    }

    public function testRegisterFailsWhenPasswordConfirmationMismatch(): void
    {
        $response = $this->request(
            'POST',
            self::REGISTER_ENDPOINT_URL,
            [
                'name'                  => 'Test',
                'email'                 => 'test_' . uniqid() . '@example.com',
                'password'              => 'Password123!',
                'password_confirmation' => 'Password123!wrong',
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

        $body = $response->json();

        $this->assertNotEmpty($body['content']['errors']['password']);
        $this->assertIsArray($body['content']['errors']['password']);

        $this->assertStringContainsString(
            'The password confirmation does not match.',
            $body['content']['errors']['password'][0]
        );
    }

    public function testRegisterSetsLanguageFromHeader(): void
    {
        $email = 'test_' . uniqid() . '@example.com';

        $this->request(
            'POST',
            self::REGISTER_ENDPOINT_URL,
            [
                'name'                  => 'Test',
                'email'                 => $email,
                'password'              => 'Password123!',
                'password_confirmation' => 'Password123!',
            ],
            $this->withLocale(Language::PL)
        );

        $this->assertDatabaseHas('users', [
            'email'    => $email,
            'language' => Language::PL->value,
        ]);
    }

    public static function requiredFieldProvider(): array
    {
        return [
            'name_required'     => ['name'],
            'email_required'    => ['email'],
            'password_required' => ['password'],
        ];
    }

    /**
     * @dataProvider requiredFieldProvider
     */
    public function testRegisterFailsWhenRequiredFieldMissing(string $field): void
    {
        $payload = [
            'name'                  => 'Test',
            'email'                 => 'test_' . uniqid() . '@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        unset($payload[$field]);

        $response = $this->request('POST', self::REGISTER_ENDPOINT_URL, $payload, $this->withLocale());

        $response->assertStatus(422)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                    'errors' => [
                        $field,
                    ],
                ],
            ]);
    }

    public function testRegisterFailsWithInvalidEmailFormat(): void
    {
        $response = $this->request(
            'POST',
            self::REGISTER_ENDPOINT_URL,
            [
                'name'                  => 'Test',
                'email'                 => 'invalid-email-format',
                'password'              => 'Password123!',
                'password_confirmation' => 'Password123!',
            ],
            $this->withLocale()
        );

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

    public function testRegisterFailsWhenLocaleHeaderMissing(): void
    {
        $response = $this->request(
            'POST',
            self::REGISTER_ENDPOINT_URL,
            [
                'name'                  => 'Test',
                'email'                 => 'test_' . uniqid() . '@example.com',
                'password'              => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]
        );

        $response->assertStatus(400)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                ],
            ]);
    }

    public function testRegisterFailsWhenLocaleHeaderUnsupported(): void
    {
        $response = $this->request(
            'POST',
            self::REGISTER_ENDPOINT_URL,
            [
                'name'                  => 'Test',
                'email'                 => 'test_' . uniqid() . '@example.com',
                'password'              => 'Password123!',
                'password_confirmation' => 'Password123!',
            ],
            [
                'X-Locale' => 'de',
            ]
        );

        $response->assertStatus(422);
    }

    public function testRegisterWithRememberIssuesLongerAccessTokenTtl(): void
    {
        $response = $this->request(
            'POST',
            self::REGISTER_ENDPOINT_URL,
            [
                'name'                  => 'Test',
                'email'                 => 'test_' . uniqid() . '@example.com',
                'password'              => 'Password123!',
                'password_confirmation' => 'Password123!',
                'remember'              => true,
            ],
            $this->withLocale()
        );

        $response->assertCreated()
            ->assertJsonPath('content.expiresIn', 60 * 60 * 24 * 7);
    }
}
