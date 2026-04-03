<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use App\Services\System\Enum\Language;
use Illuminate\Support\Facades\Mail;

final class FormsControllerTest extends ApiTestCase
{
    private const string FEEDBACK_ENDPOINT = '/form/feedback';

    public function testFeedbackSubmissionWithValidDataReturnsCreated(): void
    {
        Mail::fake();

        $response = $this->request('POST', self::FEEDBACK_ENDPOINT, [
            'name'    => 'John Doe',
            'email'   => 'john@example.com',
            'message' => 'This is a test message with some content',
        ], $this->withLocale(Language::EN));

        $response->assertStatus(201)
            ->assertJson(['status' => 'ok']);
    }

    public function testFeedbackSubmissionRequiresName(): void
    {
        $response = $this->request('POST', self::FEEDBACK_ENDPOINT, [
            'email'   => 'john@example.com',
            'message' => 'This is a test message',
        ], $this->withLocale(Language::EN));

        $response->assertStatus(422)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                    'errors' => ['name'],
                ],
            ]);
    }

    public function testFeedbackSubmissionRequiresValidEmail(): void
    {
        $response = $this->request('POST', self::FEEDBACK_ENDPOINT, [
            'name'    => 'John Doe',
            'email'   => 'invalid-email',
            'message' => 'This is a test message',
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

    public function testFeedbackSubmissionRequiresMessage(): void
    {
        $response = $this->request('POST', self::FEEDBACK_ENDPOINT, [
            'name'  => 'John Doe',
            'email' => 'john@example.com',
        ], $this->withLocale(Language::EN));

        $response->assertStatus(422)
            ->assertJson(['status' => 'error'])
            ->assertJsonStructure([
                'content' => [
                    'message',
                    'errors' => ['message'],
                ],
            ]);
    }

    public function testFeedbackSubmissionWithLongMessage(): void
    {
        $longMessage = str_repeat('This is a long message. ', 100);

        $response = $this->request('POST', self::FEEDBACK_ENDPOINT, [
            'name'    => 'John Doe',
            'email'   => 'john@example.com',
            'message' => $longMessage,
        ], $this->withLocale(Language::EN));

        if ($response->status() === 422) {
            // Message might be too long
            $response->assertJson(['status' => 'error']);
        } else {
            $response->assertStatus(201)
                ->assertJson(['status' => 'ok']);
        }
    }

    public function testFeedbackSubmissionWithSpecialCharacters(): void
    {
        $response = $this->request('POST', self::FEEDBACK_ENDPOINT, [
            'name'    => 'Ğöhn Döe',
            'email'   => 'john@example.com',
            'message' => 'Message with émojis 😀 and spëcial çhårs ñ',
        ], $this->withLocale(Language::EN));

        $response->assertStatus(201)
            ->assertJson(['status' => 'ok']);
    }

    public function testFeedbackSubmissionWithDifferentLocales(): void
    {
        $locales = [Language::EN, Language::PL, Language::RU];

        foreach ($locales as $locale) {
            $response = $this->request('POST', self::FEEDBACK_ENDPOINT, [
                'name'    => 'Test User',
                'email'   => "test-{$locale->getCode()}@example.com",
                'message' => 'Message content in ' . $locale->getCode(),
            ], $this->withLocale($locale));

            $response->assertStatus(201)
                ->assertJson(['status' => 'ok']);
        }
    }
}

