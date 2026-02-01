<?php

declare(strict_types=1);

namespace App\Repositories\Other\Faq;

use App\Models\Faq;
use App\Repositories\DatabaseRepository;
use App\Services\Other\Faq\FaqRepositoryInterface;
use App\Services\System\Enum\Language;

final readonly class FaqRepository extends DatabaseRepository implements FaqRepositoryInterface
{
    public function searchFaqs(string $q, Language $language): array
    {
        $terms = array_filter(preg_split('/\s+/', $q), fn($term) => mb_strlen($term) >= 2);

        if (empty($terms)) {
            return [];
        }

        $query = $this->db->table(Faq::getTableName())
            ->where('active', true)
            ->where('language', $language->value)
            ->where(function ($query) use ($terms) {
                foreach ($terms as $term) {
                    $term = addcslashes($term, '%_');

                    $query->orWhere('title', 'LIKE', "%{$term}%")
                        ->orWhere('text', 'LIKE', "%{$term}%");
                }
            });

        return $query->select(['title', 'text'])->get()->all();
    }

    public function saveFaq(int $id, array $data): int
    {
        if ($id > 0) {
            $this->db->table(Faq::getTableName())->where('id', $id)->update($data);

            return $id;
        }

        return $this->db->table(Faq::getTableName())->insertGetId($data);
    }

    public function getBaseFaqs(Language $language): array
    {
        return $this->db->table(Faq::getTableName())
            ->where('active', true)
            ->where('language', $language->value)
            ->select(['title', 'text'])
            ->get()
            ->all();
    }
}