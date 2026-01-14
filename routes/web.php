<?php

use App\Forms\Account\Travel\TravelForm;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AdminTravelController;
use App\Http\Controllers\API\SubscriptionApiController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\Forms\FormsController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Travel\Travel\TravelController;
use App\Http\Controllers\Travel\Travel\TravelInviteController;
use App\Http\Controllers\WelcomeController;
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
Route::get('/', function (){
    return redirect('/admin');
});

Route::get('/test', function () {
    return View('test');
});

Route::get('locale/{locale}', function ($locale) {
    Session::put('locale', $locale);
    return redirect()->back();
});

//Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
Route::get('/travels/search', [WelcomeController::class, 'searchTravelPage'])->name('travels.search.page');

Route::get('/faq', [FAQController::class, 'faqPage'])->name('faq.page');
Route::match(['get', 'post'], '/feedback', [FormsController::class, 'feedback'])->name('feedback');
Route::match(['get', 'post'], '/test', [TestController::class, 'index'])->name('test.page');
Route::match(['get', 'post'], '/travel/{token}', [TravelController::class, 'index'])->name('travel.public.link');
Route::match(['get', 'post'], '/travel/email-invite/{token}/{status}', [TravelInviteController::class, 'index'])->name('travel.email.invite.link');

Route::group(['middleware' => ['auth']], function () {
    Route::get('/account', [AccountController::class, 'index'])->name('account');
});

// Admin routes
Route::group(['middleware' => ['auth'], 'prefix' => '/admin/travel'], function () {
    Route::get('{travel_id}/image/show/{image_name}', [AdminTravelController::class, 'showImage'])->name('admin.show.image');
    Route::get('image/delete/{image_id}', [AdminTravelController::class, 'deleteImage'])->name('admin.delete.travel.image');
});
