<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth\Request\Auth;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "InviteUsersRequest",
    required: ["emails"],
    properties: [
        new OA\Property(
            property: "emails",
            description: "List of email addresses to invite",
            type: "array",
            items: new OA\Items(type: "string", format: "email")
        ),
    ],
    type: "object"
)]
class InviteUsersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'emails'   => ['required', 'array'],
            'emails.*' => 'email',
        ];
    }
}
