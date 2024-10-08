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

Route::prefix('bus')->controller(BusController::class)->group(function () {
    Route::get('/show', 'show');
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


    Route::prefix('user')->controller(UserController::class)->group(function () {
        Route::get('/profile', 'profile');
    });

    Route::prefix('bus')->controller(BusController::class)->group(function () {
        Route::post('/add/new', 'create');
        Route::post('/update', 'update');
        Route::post('/delete', 'delete');
    });


    Route::prefix('categories')->controller(CategoriesController::class)->group(function () {
        Route::post('/add/new', 'create');
        Route::post('/add/new/image', 'addNewImage');
        Route::put('/update', 'update');
        Route::delete('/delete', 'delete');
    });

    Route::prefix('facilities')->controller(FacilitiesController::class)->group(function () {
        Route::post('/add/new/facilities', 'addFacilitiesToBus');
        Route::delete('/delete/facilities', 'deleteFacilitiesFromBus');
        Route::post('/add/new', 'create');
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
        Route::post('/add/new', 'create');
        Route::put('/update', 'update');
        Route::delete('/delete', 'delete');
    });

    Route::prefix('image_bus')->controller(ImageBusController::class)->group(function () {
        Route::post('/add/new', 'create');
        Route::delete('/delete', 'delete');
    });


});

