<?php

declare(strict_types=1);

namespace App\Services\Language;

use App\Models\System\Translate;
use App\Services\Excel\ExcelTranslateService;
use App\Services\Language\Enum\TranslateGroupEnum;
use App\Services\System\Enum\Language;
use Illuminate\Http\UploadedFile;

final readonly class TranslateService
{
    public function __construct(
        private TranslateRepositoryInterface $repository,
        private ExcelTranslateService        $excel,
    ) {}

    public function saveTranslate(int $id, array $data, array $groups): void
    {
        $this->repository->saveTranslate($id, $data, $groups);
    }

    public function getTranslateByCode(string $code, Language $language): ?string
    {
        return $this->repository->getTranslateByCode($code, $language);
    }

    public function getGroupsForTranslate(int $translateId): array
    {
        return $this->repository->getGroupsForTranslate($translateId);
    }

    public static function getFullList(Language $language): array
    {
        $list = Translate::select('code', $language->getCode())->get()->all();

        $field = $language->getCode();
        $out = [];
        foreach ($list as $value) {
            $out[$value->code] = $value->$field;
        }

        return $out;
    }

    public function importTranslateFromExcel(UploadedFile $file, int $headerRowNumber = 1): void
    {
        $data = $this->excel->parseTranslateExcel($file, $headerRowNumber);

        foreach ($data as $value) {
            $translateData = [
                'code' => $value['Code'],
                'en'   => $value['EN'],
                'ru'   => $value['RU'],
                'pl'   => $value['PL'],
            ];

            $groups = [];

            if (!empty($value['Groups'])) {
                $groupNames = array_map('trim', explode(',', $value['Groups']));

                foreach ($groupNames as $group) {
                    if (is_numeric($group)) {
                        $groups[] = (int)$group;
                    }

                    if (is_string($group)) {
                        $enum = TranslateGroupEnum::fromLabel($group);
                        $groups[] = $enum->value;
                    }
                }
            }

            $this->repository->saveTranslate((int)($value['ID'] ?? $value['id'] ?? 0), $translateData, $groups);
        }
    }

    public function purge(): void
    {
        $this->repository->purge();
    }

    public function getExportList(): array
    {
        return $this->repository->getExportList();
    }
}
