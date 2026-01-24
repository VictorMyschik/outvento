<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth\Request\Auth;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "AuthenticateRequest",
    required: ["login", "password"],
    properties: [
        new OA\Property(property: "login", type: "string", maxLength: 255),
        new OA\Property(property: "password", type: "string", example: "password123"),
        new OA\Property(property: "remember", type: "boolean", example: true),
    ],
    type: "object"
)]
class AuthenticateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'login'    => ['sometimes', 'nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function getLogin(): ?string
    {
        return $this->input('login');
    }

    public function getPassword(): string
    {
        return $this->input('password');
    }

    public function getRemember(): bool
    {
        return $this->boolean('remember', false);
    }
}
