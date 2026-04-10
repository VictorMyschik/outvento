<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\User\Request;

use Illuminate\Foundation\Http\FormRequest;

class AlbumMediaCommentIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function getPerPage(): int
    {
        return (int)$this->input('per_page', 20);
    }
}

