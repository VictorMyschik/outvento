<?php

declare(strict_types=1);

namespace App\Services\User\Google;

final readonly class JsonResponse
{
    public function __construct(public array $data) {}

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
