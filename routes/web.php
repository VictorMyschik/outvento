<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    file_put_contents(storage_path('logs/laravel.log'), '');
    return back();
})->name('clear');

Route::get('/', function () {
    return redirect('/admin');
})->name('home');

Route::get('/test', function () {
    return View('test');
});
