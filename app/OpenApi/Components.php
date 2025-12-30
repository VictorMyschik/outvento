<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Components(
    parameters: [
        new OA\Parameter(
            parameter: "AcceptHeader",
            name: "Accept",
            description: "Must be 'application/json'",
            in: "header",
            required: true,
            schema: new OA\Schema(
                type: "string",
                default: "application/json"
            )
        ),
        new OA\Parameter(
            parameter: "XRequestedWithHeader",
            name: "X-Requested-With",
            description: "Must be 'XMLHttpRequest'",
            in: "header",
            required: true,
            schema: new OA\Schema(
                type: "string",
                default: "XMLHttpRequest"
            )
        ),
        new OA\Parameter(
            parameter: "AcceptLanguageHeader",
            name: "Accept-Language",
            description: "Must be 'en', 'pl' or 'ru'",
            in: "header",
            required: false,
            schema: new OA\Schema(
                type: "string",
                default: "ru",
                enum: ["en", "pl", "ru"]
            )
        ),
    ]
)]
class Components {}
