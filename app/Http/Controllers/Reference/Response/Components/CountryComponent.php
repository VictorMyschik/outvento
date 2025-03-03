<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reference\Response\Components;

final readonly class CountryComponent
{
    public function __construct(
        public string $title,
        public array  $options,
    ) {}
}
