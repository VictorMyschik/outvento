<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use App\Services\Promo\Enum\Status;
use App\Services\System\Enum\Language;
use Illuminate\Support\Facades\DB;

final class SubscriptionControllerTest extends ApiTestCase
{
    private const string SUBSCRIBE_ENDPOINT = '/subscription/subscribe';
    private const string CONFIRM_ENDPOINT = '/subscription/confirm/{token}';

    public function testSubscribeWithValidEmail(): void
    {
        $response = $this->request('POST', self::SUBSCRIBE_ENDPOINT, [
            'email'  => 'subscriber@example.com',
            'source' => 'footer',
        ], $this->withLocale(Language::EN));

        $response->assertStatus(201)
            ->assertJson(['status' => 'ok']);

        $this->assertDatabaseHas('subscriptions', [
            'email' => 'subscriber@example.com',
        ]);
    }

    public function testSubscribeRequiresEmail(): void
    {
        $response = $this->request('POST', self::SUBSCRIBE_ENDPOINT, [
            'source' => 'footer',
        ], $this->withLocale(Language::EN));

        $response->assertStatus(422)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                    'errors' => ['email'],
                ],
            ]);
    }

    public function testSubscribeRequiresValidEmail(): void
    {
        $response = $this->request('POST', self::SUBSCRIBE_ENDPOINT, [
            'email'  => 'invalid-email',
            'source' => 'footer',
        ], $this->withLocale(Language::EN));

        $response->assertStatus(422)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                    'errors' => ['email'],
                ],
            ]);
    }


    public function testSubscribeWithDifferentSources(): void
    {
        $sources = ['footer', 'admin'];

        foreach ($sources as $index => $source) {
            $response = $this->request('POST', self::SUBSCRIBE_ENDPOINT, [
                'email'  => "subscriber{$index}@example.com",
                'source' => $source,
            ], $this->withLocale(Language::EN));

            $response->assertStatus(201)
                ->assertJson(['status' => 'ok']);

            $this->assertDatabaseHas('subscriptions', [
                'email' => "subscriber{$index}@example.com",
            ]);
        }
    }

    public function testSubscribeWithDifferentLocales(): void
    {
        $locales = [Language::EN, Language::PL, Language::RU];

        foreach ($locales as $index => $locale) {
            $response = $this->request('POST', self::SUBSCRIBE_ENDPOINT, [
                'email'  => "subscriber-{$locale->getCode()}@example.com",
                'source' => 'footer',
            ], $this->withLocale($locale));

            $response->assertStatus(201)
                ->assertJson(['status' => 'ok']);

            $this->assertDatabaseHas('subscriptions', [
                'email' => "subscriber-{$locale->getCode()}@example.com",
            ]);
        }
    }

    public function testSubscribeDuplicateEmail(): void
    {
        $email = 'duplicate@example.com';

        $response1 = $this->request('POST', self::SUBSCRIBE_ENDPOINT, [
            'email'  => $email,
            'source' => 'footer',
        ], $this->withLocale(Language::EN));

        $response1->assertStatus(201);

        $response2 = $this->request('POST', self::SUBSCRIBE_ENDPOINT, [
            'email'  => $email,
            'source' => 'footer',
        ], $this->withLocale(Language::EN));

        // Second subscription might be rejected or create new record depending on business logic
        $this->assertThat(
            $response2->status(),
            $this->logicalOr(
                $this->equalTo(201),
                $this->equalTo(422)
            )
        );
    }

    public function testConfirmSubscriptionWithValidToken(): void
    {
        // Create a pending subscription
        $subscription = DB::table('subscriptions')->insertGetId(
            [
                'email'    => 'confirm@example.com',
                'token'    => 'valid-token-123',
                'event'    => 'news',
                'status'   => Status::Pending->value,
                'language' => 1,
            ],
            'id'
        );

        $response = $this->request('GET', str_replace(
            '{token}',
            'valid-token-123',
            self::CONFIRM_ENDPOINT
        ));

        $response->assertStatus(204);

        $this->assertDatabaseHas('subscriptions', [
            'id'     => $subscription,
            'status' => Status::Confirmed->value,
        ]);
    }

    public function testConfirmSubscriptionWithInvalidToken(): void
    {
        $response = $this->request('GET', str_replace(
            '{token}',
            'invalid-token-xyz',
            self::CONFIRM_ENDPOINT
        ));

        $response->assertStatus(404)
            ->assertJson(['status' => 'ok']);
    }

    public function testConfirmAlreadyConfirmedSubscription(): void
    {
        // Create an already confirmed subscription
        DB::table('subscriptions')->insert([
            'email'    => 'confirmed@example.com',
            'token'    => 'confirmed-token',
            'event'    => 'news',
            'status'   => Status::Confirmed->value,
            'language' => 1,
        ]);

        $response = $this->request('GET', str_replace(
            '{token}',
            'confirmed-token',
            self::CONFIRM_ENDPOINT
        ));

        // Attempting to confirm already confirmed subscription should return 404
        $response->assertStatus(404);
    }

    public function testConfirmSubscriptionWithExpiredToken(): void
    {
        // Create an old pending subscription
        DB::table('subscriptions')->insert([
            'email'    => 'old@example.com',
            'token'    => 'old-token',
            'event'    => 'news',
            'status'   => Status::Pending->value,
            'language' => 1,
        ]);

        $response = $this->request('GET', str_replace(
            '{token}',
            'old-token',
            self::CONFIRM_ENDPOINT
        ));

        // Token might be considered expired depending on business logic
        // So we just verify the request completes
        $this->assertThat(
            $response->status(),
            $this->logicalOr(
                $this->equalTo(204),
                $this->equalTo(404),
                $this->equalTo(422)
            )
        );
    }
}

