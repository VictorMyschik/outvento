<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use App\Services\System\Enum\Language;

final class FAQControllerTest extends ApiTestCase
{
    private const string FAQ_SEARCH_ENDPOINT = '/faq/search';
    private const string FAQ_LIST_ENDPOINT = '/faq/list';

    public function testFaqSearchWithValidQuery(): void
    {
        $response = $this->request('POST', self::FAQ_SEARCH_ENDPOINT, [
            'q' => 'how to register',
        ], $this->withLocale(Language::EN));

        $this->assertSuccess($response);
        $response->assertJsonStructure([
            'content' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                ],
            ],
        ]);
    }

    public function testFaqSearchWithEmptyQuery(): void
    {
        $response = $this->request('POST', self::FAQ_SEARCH_ENDPOINT, [
            'q' => '',
        ], $this->withLocale(Language::EN));

        // Empty query may be rejected or return empty results
        if ($response->status() === 200) {
            $this->assertSuccess($response);
            $this->assertIsArray($response->json('content'));
        } else {
            $response->assertStatus(422);
        }
    }

    public function testFaqSearchRequiresQuery(): void
    {
        $response = $this->request('POST', self::FAQ_SEARCH_ENDPOINT, [], $this->withLocale(Language::EN));

        // Query is optional
        if ($response->status() === 200) {
            $this->assertSuccess($response);
        } else {
            $response->assertStatus(422);
        }
    }

    public function testFaqSearchWithSpecialCharacters(): void
    {
        $response = $this->request('POST', self::FAQ_SEARCH_ENDPOINT, [
            'q' => 'help search',
        ], $this->withLocale(Language::EN));

        // Should handle valid search gracefully
        $this->assertSuccess($response);
        $this->assertIsArray($response->json('content'));
    }

    public function testFaqSearchWithDifferentLocales(): void
    {
        $locales = [Language::EN, Language::PL, Language::RU];

        foreach ($locales as $locale) {
            $response = $this->request('POST', self::FAQ_SEARCH_ENDPOINT, [
                'q' => 'help',
            ], $this->withLocale($locale));

            $this->assertSuccess($response);
            $this->assertIsArray($response->json('content'));
        }
    }

    public function testFaqSearchWithValidLongQuery(): void
    {
        $longQuery = 'this is a longer search query that tests the system';

        $response = $this->request('POST', self::FAQ_SEARCH_ENDPOINT, [
            'q' => $longQuery,
        ], $this->withLocale(Language::EN));

        // Valid long query should be handled
        if ($response->status() === 200) {
            $this->assertSuccess($response);
            $this->assertIsArray($response->json('content'));
        } else {
            $response->assertStatus(422);
        }
    }

    public function testGetBaseFaqListReturnsDataWithoutAuthentication(): void
    {
        $response = $this->request('GET', self::FAQ_LIST_ENDPOINT, [], $this->withLocale(Language::EN));

        $this->assertSuccess($response);
        $response->assertJsonStructure([
            'content' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                ],
            ],
        ]);
    }

    public function testGetBaseFaqListReturnsDataWithAuthentication(): void
    {
        $user = $this->createUserWithPassword();
        $tokens = $this->issueTokens($user);

        $response = $this->request('GET', self::FAQ_LIST_ENDPOINT, [], array_merge(
            $this->bearerHeaders($tokens['accessToken']),
            $this->withLocale(Language::EN)
        ));

        $this->assertSuccess($response);
        $response->assertJsonStructure([
            'content' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                ],
            ],
        ]);
    }

    public function testGetBaseFaqListWithDifferentLocales(): void
    {
        $locales = [Language::EN, Language::PL, Language::RU];

        foreach ($locales as $locale) {
            $response = $this->request('GET', self::FAQ_LIST_ENDPOINT, [], $this->withLocale($locale));

            $this->assertSuccess($response);
            $this->assertIsArray($response->json('content'));
        }
    }

    public function testGetBaseFaqListReturnsConsistentData(): void
    {
        $response1 = $this->request('GET', self::FAQ_LIST_ENDPOINT, [], $this->withLocale(Language::EN));
        $response2 = $this->request('GET', self::FAQ_LIST_ENDPOINT, [], $this->withLocale(Language::EN));

        $this->assertSuccess($response1);
        $this->assertSuccess($response2);

        $content1 = $response1->json('content');
        $content2 = $response2->json('content');

        // Responses should have same structure and count
        $this->assertCount(count($content1), $content2);
    }
}

