<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Albums\AlbumRepository;
use App\Services\Albums\AlbumRepositoryInterface;
use App\Services\Albums\AlbumService;
use App\Services\Albums\AlbumUploadService;
use App\Services\Image\AlbumImageResizer;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

class AlbumProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AlbumRepositoryInterface::class, function (Application $app) {
            return new AlbumRepository($app->make(DatabaseManager::class));
        });

        $this->app->bind(AlbumService::class, function (Application $app) {
            $config = $this->app->make(Repository::class)->get('storage')['albums'];

            return new AlbumService(
                repository: $app->make(AlbumRepositoryInterface::class),
                uploadService: $app->make(AlbumUploadService::class),
            );
        });

        $this->app->singleton(AlbumUploadService::class, function ($app) {
            $config = $this->app->make(Repository::class)->get('storage')['albums'];

            return new AlbumUploadService(
                filesystem: $app->make(Factory::class)->disk($config['disk']),
                repository: $app->make(AlbumRepositoryInterface::class),
                imageResizer: $app->make(AlbumImageResizer::class),
                basePaths: $config,
            );
        });
    }
}