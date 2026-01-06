<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Constructor\ConstructorRepository;
use App\Services\Constructor\ConstructorRepositoryInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

class ConstructorProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ConstructorRepositoryInterface::class, function (Application $app) {
            return new ConstructorRepository($app->make(DatabaseManager::class));
        });
    }
}