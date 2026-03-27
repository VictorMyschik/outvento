<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Constructor\ConstructorRepository;
use App\Repositories\Conversations\ConversationRepository;
use App\Services\Constructor\ConstructorRepositoryInterface;
use App\Services\Conversations\ConversationRepositoryInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

class ConversationProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ConversationRepositoryInterface::class, function (Application $app) {
            return new ConversationRepository($app->make(DatabaseManager::class));
        });
    }
}