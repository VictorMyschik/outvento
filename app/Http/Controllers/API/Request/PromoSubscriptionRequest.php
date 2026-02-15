<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Request;

use Illuminate\Foundation\Http\FormRequest;

class PromoSubscriptionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email'  => 'required|email',
            'source' => 'nullable|string|max:255',
        ];
    }

    public function getEmail(): string
    {
       return $this->input('email');
    }
}