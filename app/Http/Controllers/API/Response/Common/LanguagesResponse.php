<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Response\Common;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "LanguagesResponse",
    description: "Response containing available languages mapped by locale code",
    required: ["en", "ru", "pl"],
    properties: [
        new OA\Property(property: "en", description: "English label", type: "string", example: "English"),
        new OA\Property(property: "ru", description: "Russian label", type: "string", example: "Русский"),
        new OA\Property(property: "pl", description: "Polish label", type: "string", example: "Polski"),
    ],
    type: "object",
    example: ["en" => "English", "ru" => "Русский", "pl" => "Polski"]
)]
final readonly class LanguagesResponse
{
    public function __construct(
        public string $en,
        public string $ru,
        public string $pl,
    ) {}
}