<?php

declare(strict_types=1);

namespace App\Services\References\API\Response\Components;

final readonly class TravelTypeComponent
{
    public function __construct(
        public string $title,
        public array  $options,
    ) {}
}
