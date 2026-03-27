<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Profile\ProfilePageController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Category\CategoryPageController;
use App\Http\Controllers\Service\ServiceController;
use App\Http\Controllers\Service\ServicePageController;
use App\Http\Controllers\JobRequest\JobRequestController;
use App\Http\Controllers\JobRequest\JobRequestPageController;
use App\Http\Controllers\Service\ServiceApplicationController;
use App\Http\Controllers\Service\ServiceBrowsePageController;
use App\Http\Controllers\Category\CategoryBrowsePageController;
use App\Http\Controllers\ServiceApplication\MyServiceApplicationsPageController;
use App\Http\Controllers\JobRequest\JobRequestCategoryBrowsePageController;
use App\Http\Controllers\JobRequest\JobRequestBrowsePageController;
use App\Http\Controllers\Application\ApplicationController;
use App\Http\Controllers\Application\ApplicationPageController;
use App\Http\Controllers\Review\ReviewController;
use App\Http\Controllers\Profile\MasterPublicProfileController;
use App\Http\Controllers\Profile\SeekerPublicProfileController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {

    // Informācijas panelis un profils
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfilePageController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // Administratora panelis
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [CategoryPageController::class, 'index'])->name('index');
        });
    });

    // Meistara panelis
    Route::middleware(['role:master'])->prefix('master')->name('master.')->group(function () {
        Route::get('/my-services',       [ServicePageController::class,                   'index'])->name('services.index');
        Route::get('/browse-categories', [JobRequestCategoryBrowsePageController::class,  'index'])->name('job-requests.categories');
        Route::get('/browse-jobs',       [JobRequestBrowsePageController::class,          'index'])->name('job-requests.index');
        Route::get('/my-applications',   [ApplicationPageController::class,               'index'])->name('applications.index');
    });

    // Publiskie profili (pieejami abām lomām)
    Route::get('/meistars/{id}',  [MasterPublicProfileController::class,  'show'])->name('master.public-profile');
    Route::get('/mekletajs/{id}', [SeekerPublicProfileController::class,  'show'])->name('seeker.public-profile');

    // Meklētāja panelis
    Route::middleware(['role:seeker'])->prefix('seeker')->name('seeker.')->group(function () {
        Route::get('/my-requests', [JobRequestPageController::class, 'index'])->name('job-requests.index');
        Route::get('/categories', [CategoryBrowsePageController::class, 'index'])->name('categories.index');
        Route::get('/services', [ServiceBrowsePageController::class, 'index'])->name('services.index');
        Route::get('/my-applications', [MyServiceApplicationsPageController::class, 'index'])->name('service-applications.index');
    });

});

Route::middleware(['auth', 'verified'])->prefix('api')->name('api.')->group(function () {

    // API: Kategorijas
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/{id}', [CategoryController::class, 'show'])->name('show');

        Route::middleware(['role:admin'])->group(function () {
            Route::post('/', [CategoryController::class, 'store'])->name('store');
            Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
            Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
        });
    });

    // API: Pakalpojumi
    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/', [ServiceController::class, 'index'])->name('index');
        Route::get('/{id}', [ServiceController::class, 'show'])->name('show');
        
        Route::middleware(['role:master'])->group(function () {
            Route::get('/my/list', [ServiceController::class, 'myServices'])->name('my-services');
            Route::post('/', [ServiceController::class, 'store'])->name('store');
            Route::put('/{service}', [ServiceController::class, 'update'])->name('update');
            Route::delete('/{service}', [ServiceController::class, 'destroy'])->name('destroy');
        });
    });

    // API: Pakalpojumu pieteikumi (meklētāji)
    Route::middleware(['role:seeker'])->prefix('service-applications')->name('service-applications.')->group(function () {
        Route::post('/', [ServiceApplicationController::class, 'store'])->name('store');
        Route::delete('/{application}', [ServiceApplicationController::class, 'destroy'])->name('destroy');
    });

    // API: Meistaru pieteikumi uz darba sludinājumiem
    Route::middleware(['role:master'])->prefix('applications')->name('applications.')->group(function () {
        Route::post('/',                [ApplicationController::class, 'store'])->name('store');
        Route::delete('/{application}', [ApplicationController::class, 'destroy'])->name('destroy');
    });

    // API: Pieteikumu pārvaldība (meklētāji pieņem/noraida)
    Route::middleware(['role:seeker'])->prefix('applications')->name('applications.')->group(function () {
        Route::get('/', [ApplicationController::class, 'index'])->name('index');
        Route::patch('/{application}/accept', [ApplicationController::class, 'accept'])->name('accept');
        Route::patch('/{application}/reject', [ApplicationController::class, 'reject'])->name('reject');
    });

    // API: Atsauksmes
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // API: Darba sludinājumi
    Route::prefix('job-requests')->name('job-requests.')->group(function () {
        Route::get('/', [JobRequestController::class, 'index'])->name('index');
        Route::get('/{id}', [JobRequestController::class, 'show'])->name('show');

        Route::middleware(['role:seeker'])->group(function () {
            Route::get('/my/list', [JobRequestController::class, 'myRequests'])->name('my-requests');
            Route::post('/', [JobRequestController::class, 'store'])->name('store');
            Route::put('/{job_request}', [JobRequestController::class, 'update'])->name('update');
            Route::delete('/{job_request}', [JobRequestController::class, 'destroy'])->name('destroy');
            Route::patch('/{job_request}/complete', [JobRequestController::class, 'complete'])->name('complete');
        });
    });

});

require __DIR__.'/auth.php';