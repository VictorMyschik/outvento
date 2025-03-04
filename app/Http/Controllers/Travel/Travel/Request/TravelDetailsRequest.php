<?php

declare(strict_types=1);

namespace App\Http\Controllers\Travel\Travel\Request;

use Illuminate\Foundation\Http\FormRequest;

class TravelDetailsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'travel_id' => 'required|int|exists:travels,id',
        ];
    }

    public function getTravelId(): int
    {
        return (int)$this->get('travel_id');
    }
}
