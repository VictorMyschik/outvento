<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Response\Common;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "LanguagesResponse",
    description: "Response containing available languages mapped by locale code",
    required: ["languages"],
    properties: [
        new OA\Property(
            property: "languages",
            description: "Mapping of locale code to label",
            type: "object",
            additionalProperties: new OA\AdditionalProperties(type: "string", example: "English")
        ),
    ],
    type: "object",
    example: [
        "languages" => ["en" => "English", "ru" => "Русский", "pl" => "Polski"]
    ]
)]
final readonly class LanguagesResponse
{
    public function __construct(
        public array $languages,
    ) {}
}