<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth\Request\Auth;

use App\Models\User;
use App\Support\Validation\PasswordRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "RegisterRequest",
    required: ["name", "email", "password"],
    properties: [
        new OA\Property(property: "name", type: "string", maxLength: 255, example: "John"),
        new OA\Property(property: "email", type: "string", format: "email", maxLength: 255, example: "john.doe@example.com"),
        new OA\Property(property: "password", type: "string", format: "password", example: "password123"),
        new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "password123"),
        new OA\Property(property: "remember", type: "boolean", example: true),
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
            'password' => ['required', 'string', PasswordRules::default(), 'confirmed'],
            'remember' => ['sometimes', 'boolean'],
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

    public function remember(): bool
    {
        return $this->boolean('remember', false);
    }

    public function messages(): array
    {
        return [
            'name.required'      => __('register.name_required'),
            'email.required'     => __('register.email_required'),
            'email.email'        => __('register.email_email'),
            'password.required'  => __('register.password_required'),
            'password.confirmed' => __('register.password_confirmed'),
            'password.min'       => __('register.password_min'),
            'password.mixed'     => __('register.password_mixed'),
            'password.numbers'   => __('register.password_numbers'),
            'password.symbols'   => __('register.password_symbols'),
        ];
    }
}
