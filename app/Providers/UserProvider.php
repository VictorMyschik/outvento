<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\UploadService\UploadServiceDBRepository;
use App\Repositories\User\UserRepository;
use App\Services\Language\TranslateService;
use App\Services\Upload\UploadService;
use App\Services\User\UserService;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class UserProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserService::class, function ($app) {
            return new UserService(
                translateService: $app->make(TranslateService::class),
                uploadService: new UploadService(
                    $app->make(Filesystem::class),
                    $app->make(UploadServiceDBRepository::class),
                ),
                repository: $app->make(UserRepository::class),
            );
        });
    }
}