<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\User\UserRepository;
use App\Services\Notifications\ServiceNotificationService;
use App\Services\Notifications\SystemNotificationService;
use App\Services\Travel\TravelService;
use App\Services\User\AuthService;
use App\Services\User\Google\GoogleAPIAdapter;
use App\Services\User\Google\GoogleClient;
use App\Services\User\GoogleApiInterface;
use App\Services\User\UserService;
use App\Services\User\UserUploadService;
use GuzzleHttp\Client;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class UserProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserService::class, function ($app) {
            $config = $app->make(Repository::class)->get('storage')['users'];

            return new UserService(
                uploadService: new UserUploadService(
                    filesystem: $app->make(Factory::class)->disk($config['disk']),
                    basePaths: $config,
                ),
                repository: $app->make(UserRepository::class),
                authService: $app->make(AuthService::class),
                notificationService: $app->make(SystemNotificationService::class),
                serviceNotificationService: $app->make(ServiceNotificationService::class),
                travelService: $app->make(TravelService::class),
            );
        });

        $this->app->singleton(GoogleApiInterface::class, function (Application $app) {
            return new GoogleAPIAdapter(
                new GoogleClient(
                    new Client(),
                    $app->make(LoggerInterface::class),
                ),
                $app->make(Repository::class)->get('services')['google'],
            );
        });
    }
}