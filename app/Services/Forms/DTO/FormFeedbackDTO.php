<?php

declare(strict_types=1);

namespace App\Services\Forms\DTO;

use App\Services\Forms\FormInterface;
use App\Services\Notifications\Enum\NotificationType;
use App\Services\System\Enum\Language;

final class FormFeedbackDTO implements FormInterface
{
    private int $id;

    public function __construct(
        public Language $language,
        public string   $name,
        public string   $email,
        public string   $message,
    ) {}

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function setID(int $id): void
    {
        $this->id = $id;
    }

    public function getType(): NotificationType
    {
        return NotificationType::Feedback;
    }

    public function getJson(): string
    {
        return json_encode([
            'name'    => $this->name,
            'email'   => $this->email,
            'message' => $this->message,
        ]);
    }
}
