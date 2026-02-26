<?php

declare(strict_types=1);

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// PUBISKIE MARŠRUTI
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

// KOPĪGIE MARŠRUTI
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'editView'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'deleteAccount'])->name('profile.destroy');
    
    Route::get('/', [CategoryController::class, 'apiIndex'])->name('index');
    Route::get('/{id}', [CategoryController::class, 'apiShow'])->name('show');

    Route::middleware(['role:admin'])->group(function () {
        Route::post('/', [CategoryController::class, 'apiStore'])->name('store');
        Route::put('/{category}', [CategoryController::class, 'apiUpdate'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'apiDestroy'])->name('destroy');
    });
});

// ADMINA MARŠRUTI
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    
});

// -- MEISTARA MARŠRUTI
Route::middleware(['auth', 'verified', 'role:master'])->group(function () {
});

// MEKLĒTĀJA MARŠRUTI
Route::middleware(['auth', 'verified', 'role:seeker'])->group(function () {
});

require __DIR__.'/auth.php';