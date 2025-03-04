<?php

declare(strict_types=1);

namespace App\Http\Controllers\Travel\Travel\Response\Components;

final readonly class TravelImageComponent
{
    public function __construct(
        public bool    $logo,
        public string  $name,
        public string  $url,
        public ?string $description,
    ) {}
}
