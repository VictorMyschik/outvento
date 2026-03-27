<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\References\ImageRepository;
use App\Repositories\Travel\TravelInviteRepository;
use App\Repositories\Travel\TravelRepository;
use App\Services\References\ImageRepositoryInterface;
use App\Services\Travel\TravelInviteRepositoryInterface;
use App\Services\Travel\TravelRepositoryInterface;
use App\Services\Travel\TravelUploadService;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class TravelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TravelRepositoryInterface::class, TravelRepository::class);

        $this->app->singleton(TravelUploadService::class, function ($app) {
            $config = $app->make(Repository::class)->get('storage')['travels'];

            return new TravelUploadService(
                filesystem: $app->make(Factory::class)->disk($config['disk']),
                repository: $app->make(TravelRepositoryInterface::class),
                basePaths: $config,
            );
        });

        $this->app->singleton(TravelInviteRepositoryInterface::class, TravelInviteRepository::class);

        // TODO: не на своём месте. Проверить надобность
        $this->app->singleton(ImageRepositoryInterface::class, function (Application $application) {
            return new ImageRepository(
                $application->make(Factory::class)->disk('public'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
