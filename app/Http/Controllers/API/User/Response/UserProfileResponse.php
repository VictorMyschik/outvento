<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\User\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UserProfileResponse",
    required: ["id", "name", "email", "isVerified", "defaultLanguage"],
    properties: [
        new OA\Property(property: "id", description: "Идентификатор пользователя", type: "integer", format: "int64"),
        new OA\Property(property: "name", description: "Никнейм", type: "string"),
        new OA\Property(property: "email", description: "Адрес электронной почты пользователя", type: "string"),
        new OA\Property(property: "avatar", description: "URL аватара пользователя", type: "string", nullable: true),
        new OA\Property(property: "isVerified", description: "Указывает, подтверждена ли электронная почта пользователя", type: "boolean"),
        new OA\Property(property: "defaultLanguage", description: "Язык по умолчанию", type: "string"),
    ],
    type: "object"
)]
final readonly class UserProfileResponse
{
    public function __construct(
        public int     $id,
        public string  $name,
        public string  $email,
        public ?string $avatar,
        public bool    $isVerified,
        public ?string  $telegram,
        public string  $defaultLanguage = 'RU',
    ) {}
}
