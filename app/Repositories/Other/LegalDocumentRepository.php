<?php

declare(strict_types=1);

namespace App\Repositories\Other;

use App\Models\Other\LegalDocument;
use App\Repositories\DatabaseRepository;
use App\Services\Other\Enum\LegalDocumentType;
use App\Services\System\Enum\Language;
use Carbon\Carbon;

final readonly class LegalDocumentRepository extends DatabaseRepository
{
    public function createLegalDocument(LegalDocumentType $type, Language $language): int
    {
        return $this->db->table(LegalDocument::getTableName())->insertGetId(['language' => $language->value, 'type' => $type->value]);
    }

    public function deleteLegalDocument(int $id): void
    {
        $this->db->table(LegalDocument::getTableName())->where('id', $id)->delete();
    }

    public function saveLegalDocument(int $id, array $data): void
    {
        $this->db->table(LegalDocument::getTableName())->where('id', $id)->update($data);
    }

    public function clone(int $id): int
    {
        $term = $this->db->table(LegalDocument::getTableName())->where('id', $id)->first();

        $data = (array)$term;
        unset($data['id']);
        unset($data['active']);

        return $this->db->table(LegalDocument::getTableName())->insertGetId($data);
    }

    public function getByType(LegalDocumentType $type, Language $language, Carbon $date): \stdClass
    {
        $query = $this->db->table(LegalDocument::getTableName())
            ->where('active', true)
            ->where('type', $type->value)
            ->where('language', $language->value);

        $query->where(function ($q) use ($date) {
            $q->where('published_at', '<=', $date)
                ->orWhereNull('published_at')
                ->where('created_at', '<=', $date);
        });

        return $query->orderByDesc('published_at')->select(['text', 'published_at', 'created_at'])->first();
    }
}