<?php

declare(strict_types=1);

namespace App\Repositories\System;

use App\Models\System\Settings;
use App\Orchid\Screens\System\Enum\SettingsKey;
use App\Repositories\DatabaseRepository;

final class SettingsRepository extends DatabaseRepository implements SettingsRepositoryInterface
{
    public function getSettingsList(): array
    {
        return Settings::get()->keyBy('code_key')->all();
    }

    public function saveSetting(int $id, array $data): int
    {
        $model = Settings::loadBy($id) ?? new Settings();

        $model->setActive((bool)$data['active']);
        $model->setCategory($data['category']);
        $model->setName($data['name']);
        $model->setValue($data['value']);
        $model->setCodeKey($data['code_key']);
        $model->setDescription($data['description']);

        $model->saveOrFail();

        return $model->id();
    }

    public function getByKey(SettingsKey $key): ?Settings
    {
        return Settings::where('code_key', $key->value)->first();
    }

    public function isEnabledEmailSend(): bool
    {
        return $this->getByKey(SettingsKey::EMAIL_SERVICE)->getValue() === '1';
    }

    public function getAdminEmail(): string
    {
        return $this->getByKey(SettingsKey::ADMIN_EMAIL)->getValue();
    }
}
