<?php

declare(strict_types=1);

namespace App\Repositories\Other;

use App\Models\Other\TermsAndCondition;
use App\Repositories\DatabaseRepository;
use App\Services\System\Enum\Language;
use Carbon\Carbon;

final readonly class TermsAndConditionsRepository extends DatabaseRepository
{
    public function createTermsAndCondition(Language $language): int
    {
        return $this->db->table(TermsAndCondition::getTableName())->insertGetId(['language' => $language->value]);
    }

    public function deleteTermsAndCondition(int $id): void
    {
        $this->db->table(TermsAndCondition::getTableName())->where('id', $id)->delete();
    }

    public function saveTermsAndCondition(int $id, array $data): void
    {
        $this->db->table(TermsAndCondition::getTableName())->where('id', $id)->update($data);
    }

    public function clone(int $id): int
    {
        $term = $this->db->table(TermsAndCondition::getTableName())->where('id', $id)->first();

        $data = (array)$term;
        unset($data['id']);
        unset($data['active']);

        return $this->db->table(TermsAndCondition::getTableName())->insertGetId($data);
    }

    public function getByLanguage(Language $language, Carbon $date): \stdClass
    {
        $query = $this->db->table(TermsAndCondition::getTableName())
            ->where('active', true)
            ->where('language', $language->value);

        $query->where(function ($q) use ($date) {
            $q->where('published_at', '<=', $date)
                ->orWhereNull('published_at')
                ->where('created_at', '<=', $date);
        });

        return $query->orderByDesc('published_at')->select(['text', 'published_at', 'created_at'])->first();
    }
}