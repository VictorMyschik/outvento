<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Travel\Request;

use App\Http\Controllers\Travel\Request\InvalidArgumentException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class CreateTravelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'          => 'required|string|max:255',
            'description'    => 'string|max:8000',
            'status'         => 'required|int|in:-1,1,2',
            'country_id'     => 'required|int|exists:country,id',
            'visible_kind'   => 'required|int|in:0,1,2',
            'travel_type_id' => 'required|int|exists:travel_type,id',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new InvalidArgumentException($validator->errors()->first());
    }
}
