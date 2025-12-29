<?php

declare(strict_types=1);

namespace App\Repositories\Language;

use App\Models\System\Translate;
use App\Models\System\TranslateGroup;
use App\Repositories\DatabaseRepository;
use App\Services\Language\TranslateRepositoryInterface;

readonly class TranslateRepository extends DatabaseRepository implements TranslateRepositoryInterface
{
    public function saveTranslate(int $id, array $data, array $groups): int
    {
        if ($id > 0) {
            $this->db->beginTransaction();
            $this->db->table(Translate::getTableName())->where('id', $id)->update($data);
            $this->db->table(TranslateGroup::getTableName())->where('translate_id', $id)->delete();
            $this->db->table(TranslateGroup::getTableName())->insert(
                array_map(fn(int $group) => ['translate_id' => $id, 'group' => $group], $groups)
            );
            $this->db->commit();
            return $id;
        }

        return $this->db->table(Translate::getTableName())->insertGetId($data);
    }

    public function getGroupsForTranslate(int $translateId): array
    {
        return $this->db->table(TranslateGroup::getTableName())
            ->where('translate_id', $translateId)
            ->pluck('group')
            ->all();
    }
}
