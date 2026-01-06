<?php

namespace App\Providers;

use App\Repositories\Forms\FormDBRepository;
use App\Repositories\Language\TranslateRepository;
use App\Repositories\Subscription\SubscriptionRepository;
use App\Repositories\System\SettingsRepository;
use App\Repositories\System\SettingsRepositoryCache;
use App\Repositories\System\SettingsRepositoryInterface;
use App\Services\Forms\FormRepositoryInterface;
use App\Services\Language\TranslateRepositoryInterface;
use App\Services\Subscription\SubscriptionRepositoryInterface;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

class SystemProvider extends ServiceProvider
{
    public function register(): void
    {
        // Settings Repository
        $this->app->bind(SettingsRepositoryInterface::class, function (Application $app) {
            return new SettingsRepositoryCache(
                new SettingsRepository($app->make(DatabaseManager::class)),
                $app->make(Repository::class)
            );
        });

        // Language
        $this->app->bind(TranslateRepositoryInterface::class, function (Application $app) {
            return new TranslateRepository($app->make(DatabaseManager::class));
        });

        // Forms
        $this->app->bind(FormRepositoryInterface::class, function (Application $app) {
            return new FormDBRepository($app->make(DatabaseManager::class));
        });

        // Subscription
        $this->app->bind(SubscriptionRepositoryInterface::class, function (Application $app) {
            return new SubscriptionRepository($app->make(DatabaseManager::class));
        });
    }
}
