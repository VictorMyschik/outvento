<?php

declare(strict_types=1);

namespace App\Services\Language;

interface TranslateRepositoryInterface
{
    public function saveTranslate(int $id, array $data, array $groups): int;

    public function getGroupsForTranslate(int $translateId): array;
}
