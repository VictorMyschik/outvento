<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Subscription\SubscriptionRepository;
use App\Services\Promo\SubscriptionRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class SubscriptionProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SubscriptionRepositoryInterface::class, function ($app) {
            return new SubscriptionRepository(
                db: $app->make('db'),
            );
        });
    }
}