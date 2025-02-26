<?php

declare(strict_types=1);

namespace App\Services\References;

interface CountryRepositoryInterface
{
    public function getSelectList(): array;
}
