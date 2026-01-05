<?php

use App\Orchid\Providers\TableServiceProvider;
use App\Providers\CatalogServiceProvider;
use App\Providers\NewsletterProvider;
use App\Providers\NotificationProvider;
use App\Providers\ReferenceProvider;
use App\Providers\SupervisorProvider;
use App\Providers\SystemProvider;
use App\Providers\TravelServiceProvider;
use App\Providers\UserProvider;
use Orchid\Icons\IconServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    UserProvider::class,
    TableServiceProvider::class,
    IconServiceProvider::class,
    TravelServiceProvider::class,
    ReferenceProvider::class,
    SystemProvider::class,
    CatalogServiceProvider::class,
    SupervisorProvider::class,
    NotificationProvider::class,
    NewsletterProvider::class,
];
