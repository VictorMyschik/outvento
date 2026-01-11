<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "AuthError",
    properties: [
        new OA\Property(property: "status", type: "string", example: "error"),
        new OA\Property(
            property: "content",
            properties: [
                new OA\Property(property: "message", type: "string", example: "Access denied"),
            ],
            type: "object"
        ),
    ],
    type: "object"
)]
class AuthError {}
