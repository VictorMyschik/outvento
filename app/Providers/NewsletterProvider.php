<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Constructor\ConstructorRepository;
use App\Repositories\Newsletter\NewsRepository;
use App\Services\Constructor\ConstructorRepositoryInterface;
use App\Services\Newsletter\ImageUploader\NewsMediaUploader;
use App\Services\Newsletter\NewsRepositoryInterface;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

class NewsletterProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ConstructorRepositoryInterface::class, function (Application $app) {
            return new ConstructorRepository($app->make(DatabaseManager::class));
        });

        $this->app->bind(NewsRepositoryInterface::class, function (Application $app) {
            return new NewsRepository($app->make(DatabaseManager::class));
        });

        $this->app->bind(NewsMediaUploader::class, function (Application $app) {
            return new NewsMediaUploader(
                filesystem: $app->make(Factory::class)->disk('public'),
                imageRepository: $app->make(NewsRepositoryInterface::class),
                storageConfig: $app->make(Repository::class)->get('storage')
            );
        });
    }
}