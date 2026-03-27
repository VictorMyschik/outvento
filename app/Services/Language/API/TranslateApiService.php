<?php

declare(strict_types=1);

namespace App\Services\Language\API;

use App\Services\Language\Enum\TranslateGroupEnum;
use App\Services\Language\TranslateRepositoryInterface;
use App\Services\System\Enum\Language;

final readonly class TranslateApiService
{
    public function __construct(private TranslateRepositoryInterface $repository) {}

    public function getTranslateFor(array $groups, Language $language): array
    {
        $translations = [];

        foreach ($groups as $group) {
            $namespace = $group->getCode();

            foreach ($this->getFromFile($group, $language) as $key => $value) {
                $currentNamespace = $namespace . '.' . $key;
                $translations = array_merge($translations, $this->generateString($currentNamespace, $value));
            }

            foreach ($this->repository->getTranslateForGroup($group, $language) as $key => $item) {
                $translations[$namespace . '.' . $key] = $item;
            }
        }

        return $translations;
    }

    private function getFromFile(TranslateGroupEnum $group, Language $language): array
    {
        $result = trans($group->getCode(), [], $language->getCode());

        return is_string($result) ? [] : $result;
    }

    private function generateString(string $namespace, array|string $fromFile): array
    {
        $out = [];

        if (is_array($fromFile)) {
            foreach ($fromFile as $key => $value) {

                $currentNamespace = $namespace . '.' . $key;

                if (is_string($value)) {
                    $out[$currentNamespace] = $value;
                    continue;
                }

                if (is_array($value)) {
                    $out = array_merge($out, $this->generateString($currentNamespace, $value));
                } else {
                    return $value;
                }
            }
        }

        if (is_string($fromFile)) {
            $out[$namespace] = $fromFile;
        }

        return $out;
    }
}