<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ValidationError",
    properties: [
        new OA\Property(property: "status", type: "string", example: "error"),
        new OA\Property(
            property: "content",
            properties: [
                new OA\Property(property: "message", type: "string", example: "Ошибка валидации"),
                new OA\Property(
                    property: "errors",
                    type: "object",
                    example: [
                        "email" => ["Проверьте payload"]
                    ],
                    additionalProperties: new OA\AdditionalProperties(
                        type: "array",
                        items: new OA\Items(type: "string", example: "Проверьте payload")
                    )
                )
            ],
            type: "object"
        )
    ],
    type: "object"
)]
class ValidationError {}
