<?php

declare(strict_types=1);

namespace App\Services\Other\Response;

final readonly class TermsAndConditionResponse
{
    public function __construct(
        public string $text,
        public ?string $publishedAt,
    ) {}
}