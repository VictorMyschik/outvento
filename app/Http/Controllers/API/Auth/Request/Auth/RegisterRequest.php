<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth\Request\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "RegisterRequest",
    required: ["name", "email", "password"],
    properties: [
        new OA\Property(property: "name", type: "string", maxLength: 255, example: "John"),
        new OA\Property(property: "email", type: "string", format: "email", maxLength: 255, example: "john.doe@example.com"),
        new OA\Property(property: "password", type: "string", format: "password", example: "password123"),
        new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "password123"),
    ],
    type: "object"
)]
class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255', Rule::unique(User::class)],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'string', Password::default(), 'confirmed'],
        ];
    }

    public function getName(): string
    {
        return $this->input('name');
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
