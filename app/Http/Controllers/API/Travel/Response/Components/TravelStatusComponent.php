<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Travel\Response\Components;

class TravelStatusComponent
{
    public function __construct(
        public int    $key,
        public string $name,
    ) {}
}
