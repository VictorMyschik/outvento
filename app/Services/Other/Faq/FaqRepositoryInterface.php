<?php

namespace App\Services\Other\Faq;

use App\Services\System\Enum\Language;

interface FaqRepositoryInterface
{
    public function searchFaqs(string $q, Language $language): array;

    public function saveFaq(int $id, array $data): int;

    public function getBaseFaqs(Language $language): array;
}