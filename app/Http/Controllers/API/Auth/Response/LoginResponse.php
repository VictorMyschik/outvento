<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "LoginResponseContent",
    required: ["token"],
    properties: [
        new OA\Property(
            property: "token",
            type: "string",
            enum: ["ok"],
            example: "54|zSA0wCsYADhnwylBCo86NPCKnz1jEYGns7cJbY8543e3b2ad",
        )
    ],
    type: "object"
)]
final readonly class LoginResponse
{
    public function __construct(
        public string $token,
    ) {}
}