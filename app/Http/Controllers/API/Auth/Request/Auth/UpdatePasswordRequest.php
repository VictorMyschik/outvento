<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth\Request\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UpdatePasswordRequest",
    required: ["current_password", "password"],
    properties: [
        new OA\Property(property: "current_password", type: "string", example: "currentPassword123"),
        new OA\Property(property: "password", type: "string", format: "password", example: "newPassword123"),
        new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "newPassword123"),
    ],
    type: "object"
)]
class UpdatePasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password'         => ['required', 'string', Password::default(), 'confirmed'],
        ];
    }

    public function getPassword(): string
    {
        return $this->input('password');
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' => "Указанный пароль не совпадает с текущим паролем от вашего аккаунта",
        ];
    }
}
