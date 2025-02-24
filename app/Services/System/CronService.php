<?php

declare(strict_types=1);

namespace App\Services\System;

use App\Models\System\Cron;
use App\Orchid\Screens\System\Enum\CronKeyEnum;
use DateInterval;
use Exception;
use Illuminate\Support\Facades\Log;

final readonly class CronService
{
    public function __construct(
    ) {}

    public function setLog(string $message): void
    {
        Log::info($message);
    }

    public function runAllActive(): void
    {
        /** @var Cron $job */
        foreach (Cron::where('active', true)->get()->all() as $job) {
            if ($this->needRun($job)) {
                $this->run($job);
            }
        }
    }

    public function needRun(Cron $job): bool
    {
        $lastWork = $job->getLastWork();
        if (is_null($lastWork)) {
            return true;
        }
        $lastWork->add(new DateInterval('PT' . $job->getPeriod() . 'M'));

        return now() > $lastWork;
    }

    public function run(Cron $cron): void
    {
        try {
            match ($cron->getCronKey()) {
                CronKeyEnum::UPDATE_CATALOG => $this->globalImportService->updateCatalog(),
                CronKeyEnum::UPDATE_SHOP_GOODS => $this->globalImportService->runLoadGoods(),
                CronKeyEnum::LOAD_GOOD_METRICS => $this->globalImportService->runLoadMetrics(),
            };

            $cron->setLastWork(now());
            $cron->save();
        } catch (Exception $e) {
            $this->setLog('Wrong run cron job:' . $e->getMessage());
        }
    }
}
