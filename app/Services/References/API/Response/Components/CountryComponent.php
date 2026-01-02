<?php

declare(strict_types=1);

namespace App\Services\References\API\Response\Components;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "CountryComponent",
    required: ["id", "iso2", "label"],
    properties: [
        new OA\Property(property: "id", description: "Country ID", type: "integer", example: 1),
        new OA\Property(property: "iso2", description: "Country ISO2 code", type: "string", example: "US"),
        new OA\Property(property: "label", description: "Country name", type: "string", example: "United States"),
    ],
    type: "object"
)]
final readonly class CountryComponent
{
    public function __construct(
        public int    $id,
        public string $iso2,
        public string $label,
    ) {}
}
