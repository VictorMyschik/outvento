<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Travel\Request;

use Illuminate\Foundation\Http\FormRequest;

class TravelResourceLinkRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'travel_id' => 'required|int|exists:travels,id',
            'title'     => 'sometimes|nullable|string|max:255',
            'url'       => 'required|url|max:255',
            'sort'      => 'required|int',
        ];
    }

    public function getTravelId(): int
    {
        return (int)$this->input('travel_id');
    }
}