<?php

declare(strict_types=1);

namespace App\Repositories\Language;

use App\Models\System\Translate;
use App\Repositories\DatabaseRepository;
use App\Services\Language\TranslateRepositoryInterface;

class TranslateRepository extends DatabaseRepository implements TranslateRepositoryInterface
{
    public function saveTranslate(int $id, array $data): int
    {
        if ($id > 0) {
            $this->db->table(Translate::getTableName())->where('id', $id)->update($data);

            return $id;
        }

        return $this->db->table(Translate::getTableName())->insertGetId($data);
    }
}
