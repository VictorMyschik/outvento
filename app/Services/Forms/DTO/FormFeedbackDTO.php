<?php

declare(strict_types=1);

namespace App\Services\Forms\DTO;

use App\Services\Forms\Enum\FormType;
use App\Services\Forms\FormInterface;
use App\Services\System\Enum\Language;

final class FormFeedbackDTO implements FormInterface
{
    public function __construct(
        public Language $language,
        public string   $name,
        public string   $email,
        public string   $message,
        public ?int     $userId,
    ) {}

    public function getType(): FormType
    {
        return FormType::Feedback;
    }

    public function jsonSerialize(): array
    {
        return [
            'language' => $this->language->value,
            'type'     => $this->getType()->value,
            'sl'       => json_encode([
                'name'    => $this->name,
                'email'   => $this->email,
                'message' => $this->message,
            ]),
            'user_id'  => $this->userId,
            'contact'  => $this->email,
        ];
    }
}
