<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Response\Common;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "TermsAndConditionsResponse",
    properties: [
        new OA\Property(
            property: "text",
            description: "Terms and Conditions text",
            type: "string",
            example: "These are the terms and conditions..."
        ),
        new OA\Property(
            property: "publishedAt",
            description: "Publication date of the Terms and Conditions",
            type: "string",
            format: "date-time",
            example: "2024-01-15T10:00:00Z"
        ),
    ],
    type: "object"
)]
final readonly class TermsAndConditionsResponse
{
    public function __construct(
        public string $text,
        public string $publishedAt,
    ) {}
}