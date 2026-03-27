<?php

use App\Services\Catalog\Onliner\ImportOnlinerService;
use App\Services\Conversations\ConversationService;
use App\Services\Newsletter\NewsletterDispatchService;
use App\Services\System\CronService;
use Illuminate\Support\Facades\Schedule;
use Psr\Log\LoggerInterface;

Schedule::call(function () {
    new CronService(
        importOnlinerService: app(ImportOnlinerService::class),
        logger: app(LoggerInterface::class),
        newsletterDispatchService: app(NewsletterDispatchService::class),
    )->runAllActive();
})->everyMinute();

Schedule::call(function () {
    $service = app(ConversationService::class);
    $service->deleteRemovedMessages();
})->everyTwoHours();