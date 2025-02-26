<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\References\CountryCacheRepository;
use App\Repositories\References\CountryRepository;
use App\Services\References\CountryRepositoryInterface;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class ReferenceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Country
        $this->app->bind(CountryRepositoryInterface::class, function (Application $app) {
            return new CountryCacheRepository(
                new CountryRepository($app->make(DatabaseManager::class)),
                $app->make(Repository::class)
            );
        });
    }
}
