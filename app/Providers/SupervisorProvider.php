<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\System\Supervisor\Client;
use App\Services\System\Supervisor\SupervisorService;
use Illuminate\Support\ServiceProvider;

class SupervisorProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SupervisorService::class, function () {
            return new SupervisorService(
                new Client(
                    user: env('SUPERVISOR_USER'),
                    password: env('SUPERVISOR_PASSWORD'),
                    host: env('SUPERVISOR_HOST')
                )
            );
        });
    }
}