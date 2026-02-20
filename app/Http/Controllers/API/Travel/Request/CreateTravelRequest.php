<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Travel\Request;

use Illuminate\Foundation\Http\FormRequest;

class CreateTravelRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title'   => 'required|string|max:255',
            'preview' => 'nullable|string|max:355',
        ];
    }

    public function getInput(): array
    {
        return [
            'title'   => $this->input('title'),
            'preview' => $this->input('preview'),
        ];
    }
}
