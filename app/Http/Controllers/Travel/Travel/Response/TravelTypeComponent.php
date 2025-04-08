<?php

declare(strict_types=1);

namespace App\Http\Controllers\Travel\Travel\Response;

class TravelTypeComponent
{
    public function __construct(
        public int    $id,
        public string $name,
        public string $icon, // url
    ) {}
}
