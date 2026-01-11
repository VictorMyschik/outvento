<?php

declare(strict_types=1);

namespace App\Http\Controllers\Forms\Request;

use App\Services\Forms\DTO\FormFeedbackDTO;
use App\Services\Forms\FormInterface;
use App\Services\System\Enum\Language;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use InvalidArgumentException;

class FeedbackRequest extends FormRequest implements FormRequestInterface
{
    public function rules(): array
    {
        return [
            'name'    => 'required|string',
            'email'   => 'required|email',
            'message' => 'required|string',
        ];
    }

    public function getDto(Language $language): FormInterface
    {
        return new FormFeedbackDTO(
            language: $language,
            name: $this->get('name'),
            email: $this->get('email'),
            message: $this->get('message'),
        );
    }

    protected function failedValidation(Validator $validator)
    {
        throw new InvalidArgumentException($validator->errors()->first());
    }
}
