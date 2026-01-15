<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\User\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "CommunicationRequest",
    required: ["type", "address"],
    properties: [
        new OA\Property(property: "type", type: "integer", example: 1),
        new OA\Property(property: "address", type: "string", maxLength: 255, example: "@JonDoe", nullable: false),
        new OA\Property(property: "description", type: "string", maxLength: 8000, example: "This is a sample description.", nullable: true),
    ],
    type: "object"
)]
class CommunicationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type_id'     => ['required', 'integer', Rule::exists('communication_types', 'id')],
            'address'     => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:8000', 'not_regex:/<[^>]+>/',]
        ];
    }

    public function getTypeId(): int
    {
        return (int)$this->input('type_id');
    }

    public function getAddress(): string
    {
        return $this->input('address');
    }

    public function getDescription(): ?string
    {
        return $this->input('description');
    }

    public function getUpdateData(): array
    {
        $out = [
            'type_id' => $this->getTypeId(),
            'address' => $this->getAddress(),
        ];

        if ($this->has('description')) {
            $out['description'] = $this->input('description');
        }

        return $out;
    }
}
