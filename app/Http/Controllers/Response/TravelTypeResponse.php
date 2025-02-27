<?php

declare(strict_types=1);

namespace App\Http\Controllers\Response;

class TravelTypeResponse
{
    public function __construct(
        public int    $id,
        public string $name,
        public ?string $description,
    ) {}
}
