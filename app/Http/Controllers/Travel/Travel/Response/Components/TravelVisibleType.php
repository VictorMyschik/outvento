<?php

declare(strict_types=1);

namespace App\Http\Controllers\Travel\Travel\Response\Components;

class TravelVisibleType
{
    public function __construct(
        public int    $key,
        public string $name,
    ) {}
}
