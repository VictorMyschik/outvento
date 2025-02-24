<?php

declare(strict_types=1);

namespace App\Http\Controllers\Response;

use App\Http\Controllers\Response\Components\CountryContinentComponent;

class CountryResponse
{
    public function __construct(
        public int                       $id,
        public string                    $name,
        public CountryContinentComponent $continent,
    ) {}
}
