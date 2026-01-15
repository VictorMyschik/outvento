<?php

declare(strict_types=1);

namespace App\Services\User\Api\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UserCommunicationComponent",
    required: ["id", "type", "address"],
    properties: [
        new OA\Property(property: "id", description: "Идентификатор пользователя", type: "integer", format: "int64"),
        new OA\Property(property: "type", description: "Никнейм", type: "string"),
        new OA\Property(property: "address", description: "Адрес электронной почты пользователя", type: "string"),
        new OA\Property(property: "description", description: "URL аватара пользователя", type: "string", nullable: true),
    ],
    type: "object"
)]
final readonly class UserCommunicationComponent
{
    public function __construct(
        public int     $id,
        public string  $type,
        public string  $address,
        public ?string $description,
    ) {}
}
