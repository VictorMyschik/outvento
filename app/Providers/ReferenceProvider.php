<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\References\ReferenceCacheRepository;
use App\Repositories\References\ReferenceRepository;
use App\Services\References\ReferenceRepositoryInterface;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class ReferenceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Country
        $this->app->bind(ReferenceRepositoryInterface::class, function (Application $app) {
            return new ReferenceCacheRepository(
                new ReferenceRepository($app->make(DatabaseManager::class)),
                $app->make(Repository::class)
            );
        });
    }
}
