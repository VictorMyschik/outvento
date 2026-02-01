<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "FaqComponent",
    title: "FAQ Component",
    description: "FAQ item with title and text",
    required: ["id", "title", "text"],
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "title", type: "string", example: "How to use the API?"),
        new OA\Property(property: "text", type: "string", example: "Use the /api endpoint with a token."),
    ]
)]
final readonly class FaqComponent
{
    public function __construct(
        public int    $id,
        public string $title,
        public string $text,
    ) {}
}