<?php

declare(strict_types=1);

namespace App\Services\System;

use App\Models\System\Settings;
use App\Orchid\Screens\System\Enum\SettingsKeyEnum;
use Illuminate\Support\Facades\Cache;

final class SettingsService
{
    private const string CACHE_KEY = 'settings';

    /**
     * @var Settings[]
     */
    private array $settings = [];

    public function __construct()
    {
        $this->all();
    }

    private function all(): array
    {
        if (empty($this->settings)) {
            $this->settings = Cache::rememberForever(self::CACHE_KEY, function () {
                return Settings::get()->keyBy('code_key')->all();
            });
        }

        return $this->settings;
    }

    private function getByKey(SettingsKeyEnum $key): Settings
    {
        $setup = $this->all()[$key->value] ?? null;
        if (!$setup) {
            throw new \InvalidArgumentException('Setting with key "' . $key->value . '" not found');
        }

        return $setup;
    }

    public function saveSetting(int $id, array $data): void
    {
        $model = Settings::loadBy($id) ?? new Settings();
        $model->setActive((bool)$data['active']);
        $model->setCategory($data['category']);
        $model->setValue($data['value']);
        $model->setCodeKey($data['code_key']);
        $model->setDescription($data['description']);
        $model->saveOrFail();

        $this->clearCache();
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
