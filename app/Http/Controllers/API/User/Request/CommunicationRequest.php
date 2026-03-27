<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\User\Request;

use App\Services\User\Enum\CommunicationType;
use App\Services\User\Enum\Visibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "CommunicationRequest",
    required: ["type", "address", "visibility"],
    properties: [
        new OA\Property(property: "type", type: "integer", example: 1),
        new OA\Property(property: "address", type: "string", maxLength: 255, example: "@JonDoe", nullable: false),
        new OA\Property(property: "description", type: "string", maxLength: 8000, example: "This is a sample description.", nullable: true),
        new OA\Property(property: "visibility", type: "integer", example: 1, nullable: false),
    ],
    type: "object"
)]
class CommunicationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type'        => ['required', 'integer', Rule::in(array_keys(CommunicationType::getSelectList()))],
            'address'     => ['required', 'string', 'max:255', 'not_regex:/<[^>]+>/'],
            'description' => ['sometimes', 'nullable', 'string', 'max:8000', 'not_regex:/<[^>]+>/'],
            'visibility'  => ['sometimes', 'integer', Rule::in(array_keys(Visibility::getSelectList()))],
        ];
    }

    public function getUpdateData(): array
    {
        $out = [
            'type'       => (int)$this->input('type'),
            'address'    => $this->input('address'),
            'visibility' => $this->input('visibility', Visibility::Private->value),
        ];

        if ($this->has('description')) {
            $out['description'] = $this->input('description');
        }

        return $out;
    }
}
