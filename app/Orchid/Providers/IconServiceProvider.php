<?php

namespace App\Orchid\Providers;

use Illuminate\Support\ServiceProvider;
use Orchid\Icons\IconFinder;

class IconServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(IconFinder $iconFinder): void
    {
        $iconFinder->registerIconDirectory('fa', resource_path('icons/fontawesome'));
    }
}
