<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Telegram\Client;
use Illuminate\Config\Repository;
use Illuminate\Support\ServiceProvider;

class TelegramProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Client::class, function ($app) {
            return new Client(token: $app->make(Repository::class)->get('services.telegram-bot-api.token'));
        });
    }
}
