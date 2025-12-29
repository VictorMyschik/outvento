<?php

declare(strict_types=1);

namespace App\Services\Language;

use App\Services\Language\Enum\TranslateGroupEnum;
use App\Services\System\Enum\Language;

interface TranslateRepositoryInterface
{
    public function saveTranslate(int $id, array $data, array $groups): int;

    public function getGroupsForTranslate(int $translateId): array;

    public function getTranslateForGroup(TranslateGroupEnum $group, Language $language): array;
}
