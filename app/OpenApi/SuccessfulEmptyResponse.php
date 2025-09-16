<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "SuccessfulEmptyResponse",
    properties: [
        new OA\Property(property: "status", type: "string", example: "ok"),
        new OA\Property(property: "content", type: "null"),
    ],
    type: "object"
)]
class SuccessfulEmptyResponse {}
