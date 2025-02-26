<?php

declare(strict_types=1);

namespace App\Repositories\System;

use App\Models\System\Settings;
use App\Orchid\Screens\System\Enum\SettingsKey;
use Psr\SimpleCache\CacheInterface;

readonly class SettingsRepositoryCache implements SettingsRepositoryInterface
{
    private const string API_SETTINGS_LIST_CACHE_KEY = 'settings_list';

    public function __construct(private SettingsRepositoryInterface $repository, private CacheInterface $cache) {}

    public function getSettingsList(): array
    {
        $this->clearCache();
        return $this->cache->rememberForever(self::API_SETTINGS_LIST_CACHE_KEY, function () {
            return $this->repository->getSettingsList();
        });
    }

    public function getByKey(SettingsKey $key): ?Settings
    {
        return $this->getSettingsList()[$key->value] ?? $this->repository->getByKey($key);
    }

    public function saveSetting(int $id, array $data): int
    {
        $id = $this->repository->saveSetting($id, $data);

        $this->clearCache();

        return $id;
    }

    private function clearCache(): void
    {
        $this->cache->delete(self::API_SETTINGS_LIST_CACHE_KEY);
    }

    public function isEnabledEmailSend(): bool
    {
        $setting = $this->getByKey(SettingsKey::EMAIL_SERVICE);

        return $setting && $setting->getValue() === '1';
    }

    public function getAdminEmail(): string
    {
        return $this->getByKey(SettingsKey::ADMIN_EMAIL)->getValue();
    }
}
