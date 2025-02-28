<?php

declare(strict_types=1);

namespace App\Services\Language;

interface TranslateRepositoryInterface
{
    public function saveTranslate(int $id, array $data): int;
}
