<?php

declare(strict_types=1);

namespace App\Http\Controllers\Travel\Response\Components;

class TravelUserComponent
{
    public function __construct(
        public string $name,
        public string $email,
    ) {}
}
