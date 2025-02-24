<?php

declare(strict_types=1);

namespace App\Http\Controllers\Travel\Response\Components;

class TravelVisibleKind
{
    public function __construct(
        public int    $key,
        public string $name,
    ) {}
}
