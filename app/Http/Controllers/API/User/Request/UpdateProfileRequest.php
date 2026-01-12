<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\User\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UpdateProfileRequest",
    required: ["name", "phone", "email"],
    properties: [
        new OA\Property(property: "name", type: "string", maxLength: 255, example: "John", nullable: true),
        new OA\Property(property: "email", type: "string", format: "email", maxLength: 255, example: "user@example.com", nullable: true),
        new OA\Property(property: "telegram", type: "string", maxLength: 255, example: "123456789", nullable: true),
    ],
    type: "object"
)]
class UpdateProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'     => ['nullable', 'string', 'max:255', Rule::unique('users')->ignore(Auth::id())],
            'email'    => ['nullable', 'string', 'email', 'max:255', Rule::unique('users')->ignore(Auth::id())],
            'telegram' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function getName(): ?string
    {
        return $this->input('name');
    }

    public function getEmail(): ?string
    {
        return $this->input('email');
    }

    public function getTelegram(): ?string
    {
        return $this->input('telegram');
    }
}
