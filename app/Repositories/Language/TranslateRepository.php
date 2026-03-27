<?php

declare(strict_types=1);

namespace App\Repositories\Language;

use App\Models\System\Translate;
use App\Models\System\TranslateGroup;
use App\Repositories\DatabaseRepository;
use App\Services\Language\Enum\TranslateGroupEnum;
use App\Services\Language\TranslateRepositoryInterface;
use App\Services\System\Enum\Language;
use Illuminate\Support\Facades\DB;

readonly class TranslateRepository extends DatabaseRepository implements TranslateRepositoryInterface
{
    public function saveTranslate(int $id, array $data, array $groups): int
    {
        $tableName = Translate::getTableName();
        $this->db->beginTransaction();

        if ($id > 0) {
            $this->db->table($tableName)->where('id', $id)->updateOrInsert(['id' => $id], $data);
        } else {
            $id = $this->db->table($tableName)->insertGetId($data);
        }

        $this->db->table(TranslateGroup::getTableName())->where('translate_id', $id)->delete();
        $this->db->table(TranslateGroup::getTableName())->insert(
            array_map(fn(int $group) => ['translate_id' => $id, 'group_id' => $group], $groups)
        );

        $this->db->commit();

        return $id;
    }

    public function getGroupsForTranslate(int $translateId): array
    {
        return $this->db->table(TranslateGroup::getTableName())
            ->where('translate_id', $translateId)
            ->pluck('group_id')
            ->all();
    }

    public function getTranslateForGroup(TranslateGroupEnum $group, Language $language): array
    {
        return $this->db->table(Translate::getTableName())
            ->join(TranslateGroup::getTableName(), function ($join) use ($group) {
                $join->on(Translate::getTableName() . '.id', '=', TranslateGroup::getTableName() . '.translate_id')
                    ->where(TranslateGroup::getTableName() . '.group_id', '=', $group->value);
            })
            ->select(Translate::getTableName() . '.code', Translate::getTableName() . '.' . $language->getCode())
            ->get()
            ->mapWithKeys(function ($item) use ($language) {
                return [$item->code => $item->{$language->getCode()}];
            })->all();
    }

    public function getTranslateByCode(string $code, Language $language): ?string
    {
        return $this->db->table(Translate::getTableName())
            ->where('code', $code)
            ->value($language->getCode());
    }

    public function purge(): void
    {
        $this->db->table(Translate::getTableName())->truncate();
        $this->db->table(TranslateGroup::getTableName())->truncate();
    }

    public function getExportList(): array
    {
        $builder = $this->db->table(Translate::getTableName())
            ->leftJoin(TranslateGroup::getTableName(), function ($join) {
                $join->on(
                    Translate::getTableName() . '.id',
                    '=',
                    TranslateGroup::getTableName() . '.translate_id'
                );
            })
            ->selectRaw(
                Translate::getTableName() . '.*,
                    string_agg(' . TranslateGroup::getTableName() . '."group_id"::text, \',\') AS groups'
            )
            ->groupBy(Translate::getTableName() . '.id')
            ->orderBy('id', 'asc');

        $result = $builder->get()->all();

        $out = [];

        foreach ($result as $item) {
            $row = [
                'id'   => $item->id,
                'code' => $item->code,
            ];

            foreach (Language::list() as $language) {
                $row[$language->getCode()] = $item->{$language->getCode()};
            }

            $groupNames = [];
            if ($item->groups) {
                $groupCodes = explode(',', $item->groups);
                foreach ($groupCodes as $groupCode) {
                    $groupNames[] = TranslateGroupEnum::from((int)$groupCode)->getLabel();
                }
            }

            $row['groups'] = implode(', ', $groupNames);

            $out[] = $row;
        }

        return $out;
    }

    public function updateIndexes(): void
    {
        DB::statement("SELECT pg_catalog.setval(pg_get_serial_sequence('translates', 'id'), MAX(id)) FROM translates;");
    }
}
