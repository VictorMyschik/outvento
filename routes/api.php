<?php

declare(strict_types=1);

use App\Http\Controllers\Reference\ReferenceController;
use App\Http\Controllers\Travel\TravelController;
use Illuminate\Support\Facades\Route;


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

Route::group(['prefix' => 'reference'], function () {
    Route::post('/full', [ReferenceController::class, 'getFullReferences'])->name('api.reference.full');
    Route::post('/country/list', [ReferenceController::class, 'getUsingCountryList'])->name('api.reference.country.list');
});
