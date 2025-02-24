<?php

use App\Orchid\Providers\TableServiceProvider;
use App\Providers\TravelServiceProvider;
use Orchid\Icons\IconServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    TableServiceProvider::class,
    IconServiceProvider::class,
    TravelServiceProvider::class
];
