<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth\Request\Auth;

use App\Support\Validation\PasswordRules;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ChangePasswordRequest",
    required: ["email", "token", "password"],
    properties: [
        new OA\Property(property: "token", type: "string", example: "1d23d456s"),
        new OA\Property(property: "password", type: "string", format: "password", example: "newPassword123")
    ],
    type: "object"
)]
class ChangePasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'token'    => 'required|string',
            'password' => ['required', 'string', PasswordRules::default()],
        ];
    }
}
