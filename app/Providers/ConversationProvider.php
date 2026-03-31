<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Conversations\ConversationRepository;
use App\Services\Conversations\ConversationRepositoryInterface;
use App\Services\Conversations\ConversationService;
use App\Services\Conversations\ConversationFileService;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class ConversationProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ConversationRepositoryInterface::class, function (Application $app) {
            return new ConversationRepository($app->make(DatabaseManager::class));
        });

        $this->app->bind(ConversationService::class, function (Application $app) {
            $config = $this->app->make(Repository::class)->get('storage')['conversations'];

            return new ConversationService(
                repository: $app->make(ConversationRepositoryInterface::class),
                uploadService: $app->make(ConversationFileService::class),
                log: $app->make(LoggerInterface::class),
                config: $config,
            );
        });

        $this->app->singleton(ConversationFileService::class, function ($app) {
            $config = $this->app->make(Repository::class)->get('storage')['conversations'];

            return new ConversationFileService(
                filesystem: $app->make(Factory::class)->disk($config['disk']),
                repository: $app->make(ConversationRepositoryInterface::class),
                basePaths: $config,
            );
        });
    }
}