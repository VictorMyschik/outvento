<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Request;

use Illuminate\Foundation\Http\FormRequest;

class FAQSearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q' => 'string|max:100',
        ];
    }

    public function getSearchQuery(): string
    {
        $q = (string) $this->get('q', '');

        $q = trim($q);
        $q = mb_substr($q, 0, 100);
        $q = preg_replace('/[^\p{L}\p{N}\s\-_\'"]/u', '', $q);

        if (mb_strlen($q) < 2) {
            return '';
        }

        return $q;
    }
}