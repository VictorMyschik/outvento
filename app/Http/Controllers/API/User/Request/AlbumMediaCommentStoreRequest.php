<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\User\Request;

use Illuminate\Foundation\Http\FormRequest;

class AlbumMediaCommentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:1', 'max:2000', 'not_regex:/<[^>]+>/'],
        ];
    }

    public function getBody(): string
    {
        return trim((string)$this->input('body'));
    }
}

