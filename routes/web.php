<?php

use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

Auth::routes();

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
});

Route::post('/test', [TestController::class, 'index'])->name('test');

Route::get('locale/{locale}', function ($locale) {
    Session::put('locale', $locale);
    return redirect()->back();
});

Route::match(['get', 'post'], '/test', [TestController::class, 'index'])->name('test.page');
