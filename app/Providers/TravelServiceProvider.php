<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Travel\TravelRepository;
use App\Services\Travel\TravelRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class TravelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TravelRepositoryInterface::class, TravelRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
