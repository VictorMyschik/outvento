<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Response;

use App\Http\Controllers\API\Response\Common\ContactsResponse;
use App\Http\Controllers\API\Response\Common\LanguagesResponse;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "FrontendSettingsResponse",
    description: "Response containing frontend settings including languages and contacts",
    required: ["languages", "contacts"],
    properties: [
        new OA\Property(
            property: "languages",
            ref: "#/components/schemas/LanguagesResponse",
            description: "Mapping of locale code to label",
            type: "object",
        ),
        new OA\Property(
            property: "contacts",
            ref: "#/components/schemas/ContactsResponse",
            type: "object"
        ),
        new OA\Property(
            property: "translations",
            description: "Translations key-value pairs",
            type: "object",
            additionalProperties: new OA\AdditionalProperties(
                type: "string",
                example: "Translated Text"
            )
        ),
    ],
    type: "object",
)]
final readonly class FrontendSettingsResponse
{
    public function __construct(
        public LanguagesResponse $languages,
        public ContactsResponse  $contacts,
        public array             $translations
    ) {}
}