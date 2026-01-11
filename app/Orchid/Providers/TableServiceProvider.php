<?php

namespace App\Orchid\Providers;

use Illuminate\Support\ServiceProvider;
use Orchid\Screen\TD;

class TableServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        TD::macro('active', function () {
            $column = $this->column;

            $this->render(function ($datum) use ($column) {
                return view('admin.table_active', [
                    'active' => $datum->$column,
                ]);
            });

            return $this;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
