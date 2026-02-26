<?php

declare(strict_types=1);

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| PUBLISKIE MARŠRUTI
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

/*
|--------------------------------------------------------------------------
| WEB SKATU MARŠRUTI (Inertia)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    
    // -- Kopīgie Web Skati --
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'editView'])->name('edit');
        Route::patch('/', [ProfileController::class, 'updateProfile'])->name('update');
        Route::delete('/', [ProfileController::class, 'deleteAccount'])->name('destroy');
    });

    // -- ADMINA Web Skati --
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('index');
            Route::post('/', [CategoryController::class, 'store'])->name('store');
            Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
            Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
        });
         
    });

    // -- MEISTARA Web Skati --
    Route::middleware(['role:master'])->group(function () {
    });

    // -- MEKLĒTĀJA Web Skati --
    Route::middleware(['role:seeker'])->group(function () {
    });

});

/*
|--------------------------------------------------------------------------
| API MARŠRUTI (JSON atbildes)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->prefix('api')->name('api.')->group(function () {

    // -- Kategoriju API --
    Route::prefix('categories')->name('categories.')->group(function () {
        // Lasīšana
        Route::get('/', [CategoryController::class, 'apiIndex'])->name('index');
        Route::get('/{id}', [CategoryController::class, 'apiShow'])->name('show');

        // CRUD
        Route::middleware(['role:admin'])->group(function () {
            Route::post('/', [CategoryController::class, 'apiStore'])->name('store');
            Route::put('/{category}', [CategoryController::class, 'apiUpdate'])->name('update');
            Route::delete('/{category}', [CategoryController::class, 'apiDestroy'])->name('destroy');
        });
    });

    // -- Pakalpojumu (Services) API --
    Route::prefix('services')->name('services.')->group(function () {
        // Lasīšana
        Route::get('/', [ServiceController::class, 'apiIndex'])->name('index');
        Route::get('/{id}', [ServiceController::class, 'apiShow'])->name('show');
        
        // CRUD
        Route::middleware(['role:master'])->group(function () {
            Route::get('/my/list', [ServiceController::class, 'apiMyServices'])->name('my-services');
            Route::post('/', [ServiceController::class, 'apiStore'])->name('store');
            Route::put('/{service}', [ServiceController::class, 'apiUpdate'])->name('update');
            Route::delete('/{service}', [ServiceController::class, 'apiDestroy'])->name('destroy');
        });
    });

});

/*
|--------------------------------------------------------------------------
| AUTENTIFIKĀCIJAS MARŠRUTI (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';