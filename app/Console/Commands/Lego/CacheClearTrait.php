<?php

namespace App\Console\Commands\Lego;

use Illuminate\Support\Facades\Artisan;

trait CacheClearTrait
{
    public function clearCache(): void
    {
        file_put_contents(storage_path('logs/laravel.log'), '');
        $this->success('Logs: cleared');
        $this->nl();

        Artisan::call('cache:clear');
        $this->success('Cache: cleared');
        $this->nl();

        Artisan::call('view:clear');
        $this->success('View: cleared');
        $this->nl();

        Artisan::call('route:clear');
        $this->success('Route: cleared');
        $this->nl();

        Artisan::call('config:clear');
        $this->success('Config: cleared');
        $this->nl();
    }
}
