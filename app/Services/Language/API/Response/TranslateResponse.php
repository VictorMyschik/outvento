<?php

declare(strict_types=1);

namespace App\Services\Language\API\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TranslateResponse',
    description: 'Response returned by the welcome endpoint',
    required: ['translations'],
    properties: [
        new OA\Property(property: 'translations', description: 'Localization object (key => string)', type: 'object', example: "{'login': 'Вход'}"),
    ]
)]
final readonly class TranslateResponse
{
    public function __construct(
        public array $translations,
    ) {}
}