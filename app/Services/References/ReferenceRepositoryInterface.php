<?php

declare(strict_types=1);

namespace App\Services\References;

use App\Services\System\Enum\Language;

interface ReferenceRepositoryInterface
{
    public function getCountrySelectList(Language $language): array;

    public function getCurrencySelectList(): array;

    public function getUsingCountrySelectList(Language $language): array;

    public function getTravelTypeSelectList(Language $language): array;

    public function saveTravelType(int $id, array $data): int;

    public function saveCity(int $id, array $data): int;
}
