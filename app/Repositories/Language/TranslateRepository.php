<?php

declare(strict_types=1);

namespace App\Repositories\Language;

use App\Models\System\Translate;
use App\Models\System\TranslateGroup;
use App\Repositories\DatabaseRepository;
use App\Services\Language\Enum\TranslateGroupEnum;
use App\Services\Language\TranslateRepositoryInterface;
use App\Services\System\Enum\Language;

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

    public function getTranslateForGroup(TranslateGroupEnum $group, Language $language): array
    {
        return $this->db->table(Translate::getTableName())
            ->join(TranslateGroup::getTableName(), function ($join) use ($group) {
                $join->on(Translate::getTableName() . '.id', '=', TranslateGroup::getTableName() . '.translate_id')
                    ->where(TranslateGroup::getTableName() . '.group', '=', $group->value);
            })
            ->select(Translate::getTableName() . '.code', Translate::getTableName() . '.' . $language->getCode())
            ->get()
            ->mapWithKeys(function ($item) use ($language) {
                return [$item->code => $item->{$language->getCode()}];
            })->all();
    }
}
