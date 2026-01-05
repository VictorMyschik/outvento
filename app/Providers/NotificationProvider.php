<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Notifications\NotificationRepository;
use App\Services\Notifications\NotificationRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class NotificationProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NotificationRepositoryInterface::class, function ($app) {
            return new NotificationRepository(
                db: $app->make('db'),
            );
        });
    }
}