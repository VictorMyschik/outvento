<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Request;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
        ];
    }

    public function getEmail(): string
    {
        return (string)$this->get('email');
    }
}