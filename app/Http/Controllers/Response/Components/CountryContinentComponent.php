<?php

declare(strict_types=1);

namespace App\Http\Controllers\Response\Components;

class CountryContinentComponent
{
    public function __construct(
        public string $name,
        public string $short_name,
    ) {}
}
