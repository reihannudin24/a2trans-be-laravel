<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\BusController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\FacilitiesController;
use App\Http\Controllers\ImageBusController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/add/new/user', 'addNewUser');

});

Route::prefix('categories')->controller(CategoriesController::class)->group(function () {
    Route::get('/show', 'show');
});

Route::prefix('facilities')->controller(FacilitiesController::class)->group(function () {
    Route::get('/show', 'show');
});

Route::prefix('vendor')->controller(VendorController::class)->group(function () {
    Route::get('/show', 'show');
});

Route::prefix('brand')->controller(BrandController::class)->group(function () {
    Route::get('/show', 'show');
});

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('/logout', 'logout');
    });

    Route::prefix('categories')->controller(CategoriesController::class)->group(function () {
        Route::post('/add/new', 'create');
        Route::post('/add/new/image', 'addNewImage');
        Route::put('/update', 'update');
        Route::delete('/delete', 'delete');
    });

    Route::prefix('facilities')->controller(FacilitiesController::class)->group(function () {
        Route::post('/add/new', 'createNew');
        Route::post('/add/new/image', 'addNewImage');
        Route::put('/update', 'update');
        Route::delete('/delete', 'delete');
    });

    Route::prefix('vendor')->controller(VendorController::class)->group(function () {
        Route::post('/add/new', 'create');
        Route::put('/update', 'update');
        Route::delete('/delete', 'delete');
    });

    Route::prefix('brand')->controller(BrandController::class)->group(function () {
        Route::post('/add/new', 'createNew');
        Route::put('/update', 'update');
        Route::delete('/delete', 'delete');
    });


    Route::prefix('image_bus')->controller(ImageBusController::class)->group(function () {
        Route::post('/add/new', 'createNew');
        Route::delete('/delete', 'delete');
        Route::get('/show', 'show');
    });


});

Route::prefix('bus')->controller(BusController::class)->group(function () {
    Route::post('/add/new/image/bus', 'addNewImage');
    Route::delete('/delete/image/bus', 'deleteImage');
    Route::post('/add/new/facilities', 'addNewFacilities');
    Route::delete('/delete/facilities', 'deleteFacilities');
    Route::post('/add/new', 'addNewBus');
    Route::put('/update', 'updateBus');
    Route::delete('/delete', 'deleteBus');
    Route::get('/show', 'showBus');
});





Route::prefix('user')->controller(UserController::class)->group(function () {
    Route::get('/show', 'show');
});
