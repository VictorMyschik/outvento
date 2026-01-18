<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Request;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "SubscriptionRequest",
    required: ["email"],
    properties: [
        new OA\Property(
            property: "email",
            description: "Subscriber email",
            type: "string",
            format: "email",
            example: "user@example.com"
        )
    ]
)]
class SubscriptionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
        ];
    }

    public function getEmail(): string
    {
        return mb_strtolower($this->get('email'));
    }
}
