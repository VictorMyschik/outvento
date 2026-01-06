<?php

declare(strict_types=1);

namespace App\Services\Travel\Api\Components;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "TravelTypeComponent",
    description: "Travel type response model",
    required: ["id", "name", "icon"],
    properties: [
        new OA\Property(property: "id", description: "Identifier", type: "integer", format: "int64", example: 1),
        new OA\Property(property: "name", description: "Type name", type: "string", example: "Hiking"),
        new OA\Property(property: "icon", description: "Icon URL", type: "string", format: "uri", example: "https://example.com/icon.png"),
    ]
)]
final readonly class TravelTypeComponent
{
    public function __construct(
        public int    $id,
        public string $name,
        public string $icon
    ) {}
}
