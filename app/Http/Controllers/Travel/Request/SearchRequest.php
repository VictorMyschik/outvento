<?php

declare(strict_types=1);

namespace App\Http\Controllers\Travel\Request;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'country'    => 'nullable|int|exists:countries,id',
            'travelType' => 'nullable|int|exists:travel_type,id',
            'dateFrom'   => 'nullable|date',
            'dateTo'     => 'nullable|date',
        ];
    }
}
