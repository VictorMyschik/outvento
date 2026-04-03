<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "AuthTokenResponse",
    required: ["accessToken", "refreshToken", "tokenType", "expiresIn"],
    properties: [
        new OA\Property(property: "accessToken", type: "string", example: "1|abc..."),
        new OA\Property(property: "refreshToken", type: "string", example: "2|xyz..."),
        new OA\Property(property: "tokenType", type: "string", example: "Bearer"),
        new OA\Property(property: "expiresIn", type: "integer", example: 3600),
    ],
    type: "object"
)]
final readonly class AuthTokenResponse
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public string $tokenType,
        public int    $expiresIn,
    ) {}
}