<?php

declare(strict_types=1);

namespace App\Services\References;

use App\Services\System\Enum\Language;
use Illuminate\Support\Collection;

interface ReferenceRepositoryInterface
{
    public function getCountrySelectList(Language $language): array;

    public function getCurrencySelectList(): array;

    public function saveCountry(int $id, array $data): int;

    public function getUsingCountrySelectList(Language $language): array;

    public function getTravelTypeList(): Collection;

    public function saveTravelType(int $id, array $data): int;

    public function saveCity(int $id, array $data): int;
}
