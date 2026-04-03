<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth\Request\Auth;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "RefreshTokenRequest",
    required: ["login", "password"],
    properties: [
        new OA\Property(property: "login", type: "string", maxLength: 255),
        new OA\Property(property: "password", type: "string", example: "pR4(assword123^"),
        new OA\Property(property: "remember", type: "boolean", example: true),
    ],
    type: "object"
)]
class RefreshTokenRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'refreshToken' => ['required', 'string'],
        ];
    }

    public function getRefreshToken(): string
    {
        return $this->input('refreshToken');
    }
}
