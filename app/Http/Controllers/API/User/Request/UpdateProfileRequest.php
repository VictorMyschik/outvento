<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\User\Request;

use App\Services\System\Enum\Language;
use App\Services\User\Enum\Gender;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UpdateProfileRequest",
    required: ["name", "phone", "email"],
    properties: [
        new OA\Property(property: "name", type: "string", maxLength: 255, example: "John", nullable: true),
        new OA\Property(property: "email", type: "string", format: "email", maxLength: 255, example: "user@example.com", nullable: true),
        new OA\Property(property: "telegram", type: "string", maxLength: 255, example: "123456789", nullable: true),
        new OA\Property(property: "language", description: "Language ID", type: "integer", example: 1, nullable: true),
        new OA\Property(property: "first_name", type: "string", maxLength: 100, example: "John", nullable: true),
        new OA\Property(property: "last_name", type: "string", maxLength: 100, example: "Doe", nullable: true),
        new OA\Property(property: "gender", description: "Gender ID", type: "integer", example: 0, nullable: true),
        new OA\Property(property: "birthday", type: "string", format: "date", example: "1990-01-01", nullable: true),
        new OA\Property(property: "about", type: "string", maxLength: 8000, example: "This is a sample about me section.", nullable: true),
    ],
    type: "object"
)]
class UpdateProfileRequest extends FormRequest
{
    public function rules(?int $id = null): array
    {
        return [
            'name'       => ['sometimes', 'string', 'max:255', Rule::unique('users')->ignore($id ?: Auth::id())],
            'email'      => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($id ?: Auth::id())],
            'telegram'   => ['sometimes', 'nullable', 'string', 'max:255'],
            'language'   => ['sometimes', 'nullable', 'integer', Rule::enum(Language::class)],
            'first_name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'last_name'  => ['sometimes', 'nullable', 'string', 'max:100'],
            'gender'     => ['sometimes', 'nullable', 'integer', Rule::enum(Gender::class)],
            'birthday'   => ['sometimes', 'nullable', 'date', 'before_or_equal:' . Carbon::now()->toDateString()],
            'about'      => ['sometimes', 'nullable', 'string', 'max:8000', 'not_regex:/<[^>]+>/',]
        ];
    }

    public function getName(): ?string
    {
        return $this->input('name');
    }

    public function getEmail(): ?string
    {
        return $this->input('email');
    }

    public function getTelegram(): ?string
    {
        return $this->input('telegram');
    }

    public function getUpdateData(): array
    {
        $out = [];

        if ($this->has('name')) {
            $out['name'] = $this->getName();
        }

        if ($this->has('email')) {
            $out['email'] = $this->getEmail();
        }

        if ($this->has('telegram')) {
            $out['telegram_chat_id'] = $this->getTelegram();
        }

        if ($this->has('language')) {
            $out['language'] = (int)$this->input('language');
        }

        if ($this->has('first_name')) {
            $out['first_name'] = $this->input('first_name');
        }

        if ($this->has('last_name')) {
            $out['last_name'] = $this->input('last_name');
        }

        if ($this->has('gender')) {
            $out['gender'] = (int)$this->input('gender');
        }

        if ($this->has('birthday')) {
            $out['birthday'] = $this->input('birthday');
        }

        if ($this->has('about')) {
            $out['about'] = $this->input('about');
        }

        return $out;
    }
}
