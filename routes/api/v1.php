<?php

declare(strict_types=1);

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\CommonApiController;
use App\Http\Controllers\API\DownloadFileController;
use App\Http\Controllers\API\User\UsersController;
use App\Http\Controllers\API\WelcomeController;
use App\Http\Controllers\Reference\ReferenceController;
use App\Http\Controllers\Travel\Travel\TravelController;
use App\Http\Controllers\Travel\Travel\TravelImageController;
use Illuminate\Support\Facades\Route;

Route::middleware('optional:sanctum')->group(function () {
    // Список доступных языков
    Route::get('common/languages', [CommonApiController::class, 'getLanguages']);
    // Получить общие переводы для фронтенда
    Route::get('translate/common', [CommonApiController::class, 'getCommonTranslate']);

    // Страницы сайта
    Route::prefix('pages')->group(static function () {
        Route::get('welcome', [WelcomeController::class, 'index']);
    });
});

Route::middleware('guest')->group(static function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('register', [AuthController::class, 'register'])->name('register');

    Route::get('login/yandex', [AuthController::class, 'yandex'])->name('yandex');
    Route::get('login/yandex/redirect', [AuthController::class, 'yandexRedirect'])->name('yandexRedirect');

    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('reset-password/{token}/check', [AuthController::class, 'checkActualResetPasswordToken']);
    Route::post('reset-password/change', [AuthController::class, 'resetPasswordConfirm']);
});

Route::middleware('auth:sanctum')->group(static function () {
    Route::any('logout', [AuthController::class, 'logout'])->name('logout');
    Route::any('logout-all', [AuthController::class, 'logoutAllSessions'])->name('logoutAllSessions');

    Route::prefix('user')->group(static function () {
        Route::get('', [UsersController::class, 'profile']);
        // Установить локаль пользователя по умолчанию в Личном кабинете
        Route::post('locale/{locale}', [CommonApiController::class, 'setLocale']);
        Route::post('profile', [UsersController::class, 'updateProfile']);
        Route::post('password', [UsersController::class, 'changePassword']);
        Route::post('verify', [AuthController::class, 'verifyRegistration']);
        Route::post('verify/resend', [AuthController::class, 'verifyResend']);
        Route::delete('avatar', [UsersController::class, 'removeAvatar']);
    });

    Route::middleware('api-verified')->group(static function () {});
});

Route::group(['prefix' => 'travels'], function () {
    // Create Travel
    Route::post('search', [TravelController::class, 'searchTravels'])->name('api.travels.search');
    Route::post('list', [TravelController::class, 'getList'])->name('api.travel.list');
    Route::post('create', [TravelController::class, 'create'])->name('api.travel.create');
    Route::post('update', [TravelController::class, 'update'])->name('api.travel.update');
    Route::post('details', [TravelController::class, 'details'])->name('api.travel.details');
    Route::post('delete', [TravelController::class, 'delete'])->name('api.travel.delete');
    Route::post('personal/list', [TravelController::class, 'personalList'])->name('api.travel.personal.list');

    // Get list
    Route::post('image/list', [TravelImageController::class, 'getList'])->name('api.travel.image.list');
    // Upload image
    Route::post('image/upload', [TravelImageController::class, 'imageUpload'])->name('api.travel.image.upload');
    // Delete image
    Route::post('image/delete', [TravelImageController::class, 'deleteImage'])->name('api.travel.image.delete');
    // Public URL
    Route::get('{travel_id}/image/show/{image_name}', [TravelImageController::class, 'showImage'])->name('api.travel.image.get');
    // Update image description
    Route::post('image/update', [TravelImageController::class, 'updateImage'])->name('api.travel.image.update');
});

Route::get('/download', [DownloadFileController::class, 'download'])->name('download');