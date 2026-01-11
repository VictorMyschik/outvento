<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "SuccessfulEmptyResponse",
    required: ["status"],
    properties: [
        new OA\Property(property: "status", type: "string", example: "ok"),
    ],
    type: "object"
)]
class SuccessfulEmptyResponse {}
