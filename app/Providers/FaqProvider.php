<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Other\Faq\FaqRepository;
use App\Services\Other\Faq\FaqRepositoryInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class FaqProvider extends ServiceProvider
{
    public function register(): void
    {
        // Country
        $this->app->bind(FaqRepositoryInterface::class, function (Application $app) {
            return new FaqRepository(
                $app->make(DatabaseManager::class)
            );
        });
    }
}
