<?php

declare(strict_types=1);

namespace App\Services\System;

use App\Models\System\Cron;
use App\Services\Catalog\Onliner\ImportOnlinerService;
use App\Services\System\Enum\CronKeyEnum;
use DateInterval;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Psr\Log\LoggerInterface;

final readonly class CronService
{
    public function __construct(
        private ImportOnlinerService $importOnlinerService,
        private LoggerInterface      $logger,
    ) {}

    public function setLog(string $message): void
    {
        $this->logger->info($message);
    }

    public function runAllActive(): void
    {
        $this->setLog('Cron Start');

        /** @var Cron $job */
        foreach (Cron::where('active', true)->get()->all() as $job) {
            if ($this->needRun($job)) {
                $this->setLog('Run job: ' . $job->getCronKey()->getLabel());
                $this->run($job);
            }
        }

        $this->setLog('Cron End');
    }

    public function runAllActiveNow(): void
    {
        $this->setLog('Cron Start');

        /** @var Cron $job */
        foreach (Cron::where('active', true)->get()->all() as $job) {
            $this->setLog('Run job: ' . $job->getCronKey()->getLabel());
            $this->run($job);
        }

        $this->setLog('Cron End');
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
                CronKeyEnum::OnlinerCatalogGoods => $this->importOnlinerService->updateCatalogGoods(),
                CronKeyEnum::ClearLogs => $this->clearLogs(),
            };

            $cron->setLastWork(now());
            $cron->save();
        } catch (Exception $e) {
            $this->setLog('Wrong run cron job:' . $e->getMessage());
        }
    }

    private function clearLogs(): void
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        file_put_contents(storage_path('logs/laravel.log'), '');
    }
}
