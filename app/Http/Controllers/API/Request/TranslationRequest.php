<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Request;

use App\Services\Language\Enum\TranslateGroupEnum;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TranslationRequest',
    required: ['groups'],
    properties: [
        new OA\Property(
            property: 'groups',
            description: 'Array of translation group codes',
            type: 'array',
            items: new OA\Items(type: 'string', maxLength: 50),
            minItems: 1,
            example: ['auth', 'common']
        )
    ],
    type: 'object'
)]
class TranslationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'groups'   => ['required', 'array', 'min:1'],
            'groups.*' => ['string', 'max:50'],
        ];
    }

    public function getGroups(): array
    {
        $out = [];

        foreach ($this->input('groups', []) as $group) {
            $out[] = TranslateGroupEnum::fromCode($group);
        }

        return $out;
    }
}