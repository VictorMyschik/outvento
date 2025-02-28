<?php

declare(strict_types=1);

namespace App\Services\References;

use App\Services\System\Enum\Language;

interface CountryRepositoryInterface
{
    public function getSelectList(Language $language): array;
}
