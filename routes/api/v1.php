<?php

declare(strict_types=1);

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\CommonApiController;
use App\Http\Controllers\API\DownloadFileController;
use App\Http\Controllers\API\FAQController;
use App\Http\Controllers\API\FormsController;
use App\Http\Controllers\API\SocialAuthController;
use App\Http\Controllers\API\SubscriptionController;
use App\Http\Controllers\API\TelegramApiController;
use App\Http\Controllers\API\Travel\TravelController;
use App\Http\Controllers\API\User\ConversationController;
use App\Http\Controllers\API\User\UsersController;
use App\Http\Controllers\API\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::middleware('optional:sanctum')->group(function () {
    Route::post('/form/feedback', [FormsController::class, 'feedback']);

    Route::prefix('pages')->group(static function () {
        Route::get('welcome', [WelcomeController::class, 'index']);
    });

    /// Users profile
    Route::get('/user/{user}/avatar', [UsersController::class, 'getUserAvatar'])->name('user.avatar');
    Route::get('/conversation/{conversation}/avatar', [ConversationController::class, 'getConversationAvatar'])->name('conversation.avatar');
    Route::get('/travel/{travel}/image/{media}', [TravelController::class, 'getTravelAvatar'])->name('travel.image');
});

Route::middleware('guest')->group(static function () {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::any('refresh', [AuthController::class, 'refresh'])->name('refresh');

    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('reset-password/{token}/check', [AuthController::class, 'checkActualResetPasswordToken']);
    Route::post('reset-password/change', [AuthController::class, 'resetPasswordConfirm']);
});

Route::middleware('auth:sanctum')->group(static function () {
    Route::any('logout', [AuthController::class, 'logout'])->name('logout');
    Route::any('logout-all', [AuthController::class, 'logoutAllSessions'])->name('logoutAllSessions');

    Route::prefix('user')->group(static function () {
        Route::get('', [UsersController::class, 'profile'])->name('user.profile');
        Route::get('full', [UsersController::class, 'profileFull']);
        // Установить локаль пользователя по умолчанию в Личном кабинете
        Route::post('profile/edit', [UsersController::class, 'updateProfile']);
        Route::post('password', [AuthController::class, 'changePassword']);
        Route::post('verify', [AuthController::class, 'verifyRegistration']);
        Route::post('verify/resend', [AuthController::class, 'verifyResend']);
        Route::delete('avatar', [UsersController::class, 'removeAvatar']);

        // Communications
        Route::get('communications', [UsersController::class, 'getCommunications']);
        Route::post('communications', [UsersController::class, 'createCommunication']);
        Route::put('communications/{id}', [UsersController::class, 'updateCommunication']);
        Route::delete('communications/{id}', [UsersController::class, 'deleteCommunication']);
    });

    Route::middleware('api-verified')->group(static function () {
        /// Conversations
        Route::get('conversations/{conversation_id}/attachments/{attachment_id}', [ConversationController::class, 'getFile'])->name('conversation.attachment.get');
    });
});

// Orchid Admin
Route::middleware('web')->group(static function () {
    Route::get('conversations/{conversationId}/hash/{hash}', [ConversationController::class, 'getAdminFile'])->name('admin.conversation.attachment.get');
    Route::get('conversations/{conversationId}/message/{messageId}/zip', [ConversationController::class, 'getAdminFilesZip'])->name('admin.conversation.attachment.get.zip');
    Route::get('conversations/{conversationId}/file/{fileId}/show', [ConversationController::class, 'showMedia'])->name('admin.conversation.attachment.show.media');
});

Route::group(['prefix' => 'travels'], function () {
    // Create Travel
    /*  Route::post('search', [TravelController::class, 'searchTravels'])->name('api.travels.search');
      Route::post('list', [TravelController::class, 'getList'])->name('api.travel.list');
      Route::post('create', [TravelController::class, 'create'])->name('api.travel.create');
      Route::post('update', [TravelController::class, 'update'])->name('api.travel.update');
      Route::post('details', [TravelController::class, 'details'])->name('api.travel.details');
      Route::post('delete', [TravelController::class, 'delete'])->name('api.travel.delete');
      Route::post('personal/list', [TravelController::class, 'personalList'])->name('api.travel.personal.list');

      // Get list
      Route::post('image/list', [TravelMediaController::class, 'getList'])->name('api.travel.image.list');
      // Upload image
      Route::post('image/upload', [TravelMediaController::class, 'imageUpload'])->name('api.travel.image.upload');
      // Delete image
      Route::post('image/delete', [TravelMediaController::class, 'deleteImage'])->name('api.travel.image.delete');
      // Public URL
      Route::get('{travel_id}/image/show/{image_name}', [TravelMediaController::class, 'showImage'])->name('api.travel.image.get');
      // Update image description
      Route::post('image/update', [TravelMediaController::class, 'updateImage'])->name('api.travel.image.update');*/
});

Route::get('/auth/social/{provider}/redirect', [SocialAuthController::class, 'redirect']);
Route::get('/auth/social/{provider}/callback', [SocialAuthController::class, 'callback']);

Route::get('/common/languages', [CommonApiController::class, 'getLanguages']);
Route::get('/translations', [CommonApiController::class, 'getTranslations']);
Route::get('/frontend/settings', [CommonApiController::class, 'getFrontendSettings']);

Route::get('/download', [DownloadFileController::class, 'download'])->name('download');
Route::get('/legal/{type}', [CommonApiController::class, 'legal']);
Route::post('/faq/search', [FAQController::class, 'search']);
Route::get('/faq/list', [FAQController::class, 'getBaseFaqList']);

// Subscriptions
Route::post('/subscription/subscribe', [SubscriptionController::class, 'subscribe']);
Route::get('/subscription/confirm/{token}', [SubscriptionController::class, 'confirm']);

// Telegram Webhook
Route::post('/telegram', [TelegramApiController::class, 'index']);