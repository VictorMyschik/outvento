<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "WelcomeResponse",
    required: ["countries", "translates", "travelTypeList", "travelExamples"],
    properties: [
        new OA\Property(property: "countries", description: "List of countries", type: "array", items: new OA\Items(ref: "#/components/schemas/CountryComponent")),
        new OA\Property(property: "translates", description: "Translation entries", type: "object", example: "{'welcome_message': 'Welcome to our site!', 'login': 'Log In'}"),
        new OA\Property(property: "travelTypeList", description: "List of travel types", type: "array", items: new OA\Items(ref: "#/components/schemas/TravelTypeComponent")),
        new OA\Property(property: "travelExamples", description: "List of travel examples", type: "array", items: new OA\Items(ref: "#/components/schemas/TravelListByTypeComponent")),
    ],
    type: "object"
)]
final readonly class WelcomeResponse
{
    public function __construct(
        public array $countries,
        public array $translates,
        public array $travelTypeList,
        public array $travelExamples,
    ) {}
}