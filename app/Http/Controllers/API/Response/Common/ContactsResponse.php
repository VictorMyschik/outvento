<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Response\Common;

use OpenApi\Attributes as OA;


#[OA\Schema(
    schema: "ContactsResponse",
    description: "Response containing contact information",
    required: ["email", "phone"],
    properties: [
        new OA\Property(property: "email", description: "Contact email address", type: "string", example: "test@test.com"),
        new OA\Property(property: "phone", description: "Contact phone number", type: "string", example: "+4515478818"),
        new OA\Property(property: "telegram", description: "Contact Telegram handle", type: "string", example: "@test"),
    ],
    type: "object",
)]
final readonly class ContactsResponse
{
    public function __construct(
        public string $email,
        public string $phone,
        public string $telegram,
    ) {}
}