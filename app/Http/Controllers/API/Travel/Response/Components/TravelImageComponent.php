<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Travel\Response\Components;

final readonly class TravelImageComponent
{
    public function __construct(
        public bool    $logo,
        public string  $url,
        public ?string $description,
    ) {}
}
