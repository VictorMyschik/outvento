<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Constructor\ConstructorFileStorage;
use App\Repositories\Newsletter\NewsRepository;
use App\Services\Newsletter\ImageUploader\NewsMediaUploader;
use App\Services\Newsletter\NewsRepositoryInterface;
use App\Services\Newsletter\NewsService;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

class NewsletterProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NewsRepositoryInterface::class, function (Application $app) {
            return new NewsRepository($app->make(DatabaseManager::class));
        });

        $this->app->bind(NewsMediaUploader::class, function (Application $app) {
            return new NewsMediaUploader(
                filesystem: $app->make(Factory::class)->disk('public'),
                imageRepository: $app->make(NewsRepositoryInterface::class),
                basePath: $app->make(Repository::class)->get('storage')['constructor']['news']
            );
        });

        $this->app->bind(ConstructorFileStorage::class, function (Application $app) {
            return new ConstructorFileStorage(
                filesystem: $app->make(Factory::class)->disk('public'),
                basePath: $app->make(Repository::class)->get('storage')['constructor']['news']
            );
        });

        $this->app->singleton(NewsService::class, function (Application $app) {
            return new NewsService(
                repository: $app->make(NewsRepositoryInterface::class),
                mediaUploader: $app->make(NewsMediaUploader::class),
            );
        });
    }
}