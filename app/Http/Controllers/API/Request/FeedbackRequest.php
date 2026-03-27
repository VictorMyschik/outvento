<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Request;

use App\Services\Forms\DTO\FormFeedbackDTO;
use App\Services\Forms\FormInterface;
use App\Services\System\Enum\Language;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "FeedbackRequest",
    required: ["name", "email", "message"],
    properties: [
        new OA\Property(property: "name", description: "Sender name", type: "string", example: "John Doe"),
        new OA\Property(property: "email", description: "Sender email", type: "string", format: "email", example: "john@example.com"),
        new OA\Property(property: "message", description: "Message content", type: "string", maxLength: 5000)
    ]
)]
class FeedbackRequest extends FormRequest implements FormRequestInterface
{
    public function rules(): array
    {
        return [
            'name'    => 'required|string',
            'email'   => 'required|email',
            'message' => 'required|string|max:5000',
        ];
    }

    public function getDto(Language $language): FormInterface
    {
        return new FormFeedbackDTO(
            language: $language,
            name: $this->input('name'),
            email: $this->input('email'),
            message: $this->input('message'),
            userId: $this->user()?->id,
        );
    }
}
