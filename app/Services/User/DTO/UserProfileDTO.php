<?php

declare(strict_types=1);

namespace App\Services\User\DTO;

final readonly class UserProfileDTO implements \JsonSerializable
{
    public function __construct(
        public ?string $email,
        public ?string $name,
        public ?string $password = null,
        public ?string $telegram = null,
        public ?string $language = null,
    ) {}

    public function jsonSerialize(): array
    {
        $out = [];

        if ($this->email) {
            $out['email'] = $this->email;
        }

        if ($this->name) {
            $out['name'] = $this->name;
        }

        if ($this->password) {
            $out['password'] = $this->password;
        }

        if ($this->language) {
            $out['language'] = $this->language;
        }

        if ($this->telegram !== null) {
            $out['telegram_chat_id'] = $this->telegram;
        }

        return $out;
    }
}
