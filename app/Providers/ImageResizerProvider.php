<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Albums\AlbumRepositoryInterface;
use App\Services\Image\AlbumImageResizer;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Psr\Log\LoggerInterface;

class ImageResizerProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AlbumImageResizer::class, function ($app) {
            $config = $this->app->make(Repository::class)->get('storage')['albums'];

            return new AlbumImageResizer(
                manager: ImageManager::usingDriver(Driver::class),
                log: $app->make(LoggerInterface::class),
                repository: $app->make(AlbumRepositoryInterface::class),
                filesystem: $app->make(Factory::class)->disk($config['disk']),
                config: $config,
            );
        });
    }
}