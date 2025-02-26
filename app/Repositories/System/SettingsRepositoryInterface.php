<?php

declare(strict_types=1);

namespace App\Repositories\System;

use App\Models\System\Settings;
use App\Orchid\Screens\System\Enum\SettingsKey;

interface SettingsRepositoryInterface
{
    public function getSettingsList(): array;

    public function saveSetting(int $id, array $data): int;

    public function getByKey(SettingsKey $key): ?Settings;

    public function isEnabledEmailSend(): bool;

    public function getAdminEmail(): string;
}
