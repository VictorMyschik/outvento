<?php

declare(strict_types=1);

namespace App\Services\User\Api\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UserProfileResponse",
    required: ["id", "name", "email", "defaultLanguage"],
    properties: [
        new OA\Property(property: "id", description: "Идентификатор пользователя", type: "integer", format: "int64"),
        new OA\Property(property: "name", description: "Никнейм", type: "string"),
        new OA\Property(property: "email", description: "Адрес электронной почты пользователя", type: "string"),
        new OA\Property(property: "avatar", description: "URL аватара пользователя", type: "string", nullable: true),
        new OA\Property(property: "defaultLanguage", description: "Язык по умолчанию", type: "string"),
        new OA\Property(property: "isVerified", description: "Указывает, подтверждена ли электронная почта пользователя", type: "boolean"),
        new OA\Property(property: "telegram", description: "Telegram чат ID пользователя", type: "string", nullable: true),
        new OA\Property(property: "firstName", description: "Имя пользователя", type: "string", nullable: true),
        new OA\Property(property: "lastName", description: "Фамилия пользователя", type: "string", nullable: true),
        new OA\Property(property: "gender", description: "Пол пользователя", type: "string", nullable: true),
        new OA\Property(property: "birthday", description: "Дата рождения пользователя", type: "string", format: "date", nullable: true),
        new OA\Property(property: "about", description: "Информация о пользователе", type: "string", nullable: true),
        new OA\Property(property: "updatedAt", description: "Дата и время последнего обновления профиля пользователя", type: "string", format: "date-time", nullable: true),
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
        public string  $defaultLanguage,

        public ?bool   $isVerified,
        public ?string $telegram,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $gender,
        public ?string $birthday,
        public ?string $about,

        public ?string $updatedAt = null,
    ) {}
}
