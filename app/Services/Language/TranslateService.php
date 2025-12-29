<?php

declare(strict_types=1);

namespace App\Services\Language;

use App\Models\System\Translate;
use App\Services\System\Enum\Language;
use Illuminate\Support\Facades\Cache;

final readonly class TranslateService
{

    public function __construct(private TranslateRepositoryInterface $repository) {}

    public function saveTranslate(int $id, array $data, array $groups): void
    {
        $this->repository->saveTranslate($id, $data, $groups);

        $this->flush();
    }

    public function getTranslateFor(): array
    {
        
    }

    public function getGroupsForTranslate(int $translateId): array
    {
        return $this->repository->getGroupsForTranslate($translateId);
    }

    public static function getFullList(Language $language): array
    {
        return Cache::rememberForever('translate_list_' . $language->getCode(), function () use ($language) {
            $list = Translate::select('code', $language->getCode())->get()->all();

            $field = $language->getCode();
            $out = [];
            foreach ($list as $value) {
                $out[$value->code] = $value->$field;
            }

            return $out;
        });
    }

    public function flush(): void
    {
        foreach (Language::list() as $language) {
            Cache::forget('translate_list_' . $language->getCode());
        }
    }
}
