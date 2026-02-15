<?php

use App\Orchid\Providers\TableServiceProvider;
use App\Providers\CatalogServiceProvider;
use App\Providers\ConstructorProvider;
use App\Providers\FaqProvider;
use App\Providers\NewsletterProvider;
use App\Providers\NotificationProvider;
use App\Providers\ReferenceProvider;
use App\Providers\SubscriptionProvider;
use App\Providers\SupervisorProvider;
use App\Providers\SystemProvider;
use App\Providers\TelegramProvider;
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
    SubscriptionProvider::class,
    NewsletterProvider::class,
    ConstructorProvider::class,
    TelegramProvider::class,
    FaqProvider::class,
];
