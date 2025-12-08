<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth\Request\Auth;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "VerifyRegistrationRequest",
    required: ["code"],
    properties: [
        new OA\Property(property: "code", type: "string", format: "digits", example: "123456")
    ],
    type: "object"
)]
class VerifyRegistrationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => 'required|digits:6',
        ];
    }
}
