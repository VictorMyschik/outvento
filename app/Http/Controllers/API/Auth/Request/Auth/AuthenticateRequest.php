<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth\Request\Auth;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "AuthenticateRequest",
    required: ["email", "password"],
    properties: [
        new OA\Property(
            property: "email",
            type: "string",
            format: "email",
            maxLength: 255,
            example: "user@example.com"
        ),
        new OA\Property(
            property: "password",
            type: "string",
            example: "password123"
        )
    ],
    type: "object"
)]
class AuthenticateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    public function getEmail(): string
    {
        return $this->input('email');
    }

    public function getPassword(): string
    {
        return $this->input('password');
    }
}
