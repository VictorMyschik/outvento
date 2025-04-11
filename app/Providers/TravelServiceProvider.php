<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\References\ImageRepository;
use App\Repositories\Travel\TravelRepository;
use App\Services\References\ImageRepositoryInterface;
use App\Services\Travel\TravelRepositoryInterface;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class TravelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TravelRepositoryInterface::class, TravelRepository::class);
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
