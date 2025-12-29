<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Travel\Response;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "TravelTypeComponent",
    description: "Travel type response model",
    required: ["id", "name"]
)]
final readonly class TravelTypeComponent
{
    public function __construct(
        #[OA\Property(description: "Identifier", type: "integer", format: "int64", example: 1)]
        public int $id,

        #[OA\Property(description: "Type name", type: "string", example: "Hiking")]
        public string $name,

        #[OA\Property(description: "Icon URL", type: "string", format: "uri", example: "https://example.com/icon.png")]
        public string $icon
    ) {}
}
