<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Locations\LocationRepository;
use App\Services\Location\LocationRepositoryInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

class LocationProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LocationRepositoryInterface::class, function (Application $app) {
            return new LocationRepository($app->make(DatabaseManager::class));
        });
    }
}