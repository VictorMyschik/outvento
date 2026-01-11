<?php

declare(strict_types=1);

namespace App\Repositories\System;

use App\Models\System\Settings;
use App\Services\System\Enum\SettingsKey;

interface SettingsRepositoryInterface
{
    public function getSettingsList(): array;

    public function saveSetting(int $id, array $data): int;

    public function getByKey(SettingsKey $key): ?Settings;

    public function notificationEnabled(): bool;

    public function getAdminEmail(): string;
}
