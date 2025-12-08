<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth\Request\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ChangePasswordRequest",
    required: ["email", "code", "password"],
    properties: [
        new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com"),
        new OA\Property(property: "code", type: "string", example: "123456"),
        new OA\Property(property: "password", type: "string", format: "password", example: "newPassword123")
    ],
    type: "object"
)]
class ChangePasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email'    => 'required|email',
            'code'     => 'required|string',
            'password' => ['required', 'string', Password::default()],
        ];
    }
}
