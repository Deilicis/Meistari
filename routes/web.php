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
use App\Http\Controllers\Application\SeekerJobApplicationsPageController;
use App\Http\Controllers\Review\ReviewController;
use App\Http\Controllers\Profile\MasterPublicProfileController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\Profile\SeekerPublicProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SeekerController as AdminSeekerController;
use App\Http\Controllers\Admin\MasterController as AdminMasterController;
use App\Http\Controllers\Admin\AdminJobRequestController;
use App\Http\Controllers\Admin\AdminServiceController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\ComplaintController as AdminComplaintController;
use App\Http\Controllers\Complaint\ComplaintController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\JobLifecycleController;
use App\Http\Controllers\JobRequest\JobRequestShowPageController;
use App\Http\Controllers\ServiceApplication\MasterServiceApplicationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Stripe\CheckoutSuccessController;
use App\Http\Controllers\Stripe\WebhookController as StripeWebhookController;

Route::get('/', [WelcomeController::class, 'index'])->name('home');

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

Route::get('/noteikumi', fn() => Inertia::render('Static/Noteikumi'))->name('noteikumi');
Route::get('/privatuma-politika', fn() => Inertia::render('Static/PrivatumaPolitika'))->name('privatuma-politika');
Route::get('/kontakti', fn() => Inertia::render('Static/Kontakti'))->name('kontakti');

Route::middleware(['auth', 'verified'])->prefix('role')->name('role.')->group(function () {
    Route::post('/switch',     [RoleController::class, 'switch'])->name('switch');
    Route::post('/add-master', [RoleController::class, 'addMaster'])->name('add-master');
    Route::post('/add-seeker', [RoleController::class, 'addSeeker'])->name('add-seeker');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // Darba lifecycle API
    Route::prefix('jobs/{jobId}')->name('jobs.lifecycle.')->group(function () {
        Route::post('/accept-application', [JobLifecycleController::class, 'acceptApplication'])->name('accept');
        Route::post('/pay',               [JobLifecycleController::class, 'pay'])->name('pay');
        Route::post('/mark-complete',     [JobLifecycleController::class, 'markComplete'])->name('mark-complete');
        Route::post('/confirm',           [JobLifecycleController::class, 'confirm'])->name('confirm');
        Route::post('/dispute',           [JobLifecycleController::class, 'dispute'])->name('dispute');
        Route::post('/cancel',            [JobLifecycleController::class, 'cancel'])->name('cancel');
    });

    Route::get('/jobs/{jobId}/payment-success', CheckoutSuccessController::class)->name('jobs.payment.success');

    // Paziņojumi
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    // Informācijas panelis un profils
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfilePageController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // Administratora panelis (tikai admin)
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [CategoryPageController::class, 'index'])->name('index');
        });

        Route::prefix('seekers')->name('seekers.')->group(function () {
            Route::get('/',       [AdminSeekerController::class, 'index'])->name('index');
            Route::get('/{user}', [AdminSeekerController::class, 'show'])->name('show');
            Route::put('/{user}', [AdminSeekerController::class, 'update'])->name('update');
            Route::delete('/{user}', [AdminSeekerController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('masters')->name('masters.')->group(function () {
            Route::get('/',       [AdminMasterController::class, 'index'])->name('index');
            Route::get('/{user}', [AdminMasterController::class, 'show'])->name('show');
            Route::put('/{user}', [AdminMasterController::class, 'update'])->name('update');
            Route::delete('/{user}', [AdminMasterController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('job-requests')->name('job-requests.')->group(function () {
            Route::get('/',              [AdminJobRequestController::class, 'index'])->name('index');
            Route::get('/{jobRequest}',  [AdminJobRequestController::class, 'show'])->name('show');
            Route::put('/{jobRequest}',  [AdminJobRequestController::class, 'update'])->name('update');
            Route::delete('/{jobRequest}', [AdminJobRequestController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('services')->name('services.')->group(function () {
            Route::get('/',           [AdminServiceController::class, 'index'])->name('index');
            Route::get('/{service}',  [AdminServiceController::class, 'show'])->name('show');
            Route::put('/{service}',  [AdminServiceController::class, 'update'])->name('update');
            Route::delete('/{service}', [AdminServiceController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('staff')->name('staff.')->group(function () {
            Route::get('/',       [AdminStaffController::class, 'index'])->name('index');
            Route::post('/',      [AdminStaffController::class, 'store'])->name('store');
            Route::put('/{user}', [AdminStaffController::class, 'update'])->name('update');
            Route::delete('/{user}', [AdminStaffController::class, 'destroy'])->name('destroy');
        });

        Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
    });

    // Admin + Moderator (kopīgas routes)
    Route::middleware(['role:admin,moderator'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::prefix('complaints')->name('complaints.')->group(function () {
            Route::get('/',               [AdminComplaintController::class, 'index'])->name('index');
            Route::get('/{complaint}',    [AdminComplaintController::class, 'show'])->name('show');
            Route::put('/{complaint}',    [AdminComplaintController::class, 'update'])->name('update');
        });
    });

    // Meistara panelis
    Route::middleware(['role:master'])->prefix('master')->name('master.')->group(function () {
        Route::get('/my-services',             [ServicePageController::class,                  'index'])->name('services.index');
        Route::get('/browse-categories',       [JobRequestCategoryBrowsePageController::class, 'index'])->name('job-requests.categories');
        Route::get('/browse-jobs',             [JobRequestBrowsePageController::class,         'index'])->name('job-requests.index');
        Route::get('/my-applications',         [ApplicationPageController::class,              'index'])->name('applications.index');
        Route::get('/service-applications',          [MasterServiceApplicationController::class, 'index'])->name('service-applications.index');
        Route::get('/service-applications/{serviceId}', [MasterServiceApplicationController::class, 'forService'])->name('service-applications.for-service');
    });

    // Čats (pieejams abām lomām)
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::post('/start', [ChatController::class, 'startConversation'])->name('start');
        Route::get('/', [ChatController::class, 'index'])->name('index');
        Route::get('/{conversation}', [ChatController::class, 'show'])->name('show');
        Route::post('/{conversation}/messages', [ChatController::class, 'store'])->name('store');
    });

    // Publiskie profili (pieejami abām lomām)
    Route::get('/meistars/{id}',  [MasterPublicProfileController::class,  'show'])->name('master.public-profile');
    Route::get('/mekletajs/{id}', [SeekerPublicProfileController::class,  'show'])->name('seeker.public-profile');

    // Meklētāja panelis
    Route::middleware(['role:seeker'])->prefix('seeker')->name('seeker.')->group(function () {
        Route::get('/my-requests', [JobRequestPageController::class, 'index'])->name('job-requests.index');
        Route::get('/my-requests/{id}', [JobRequestShowPageController::class, 'show'])->name('job-requests.show');
        Route::get('/categories', [CategoryBrowsePageController::class, 'index'])->name('categories.index');
        Route::get('/services', [ServiceBrowsePageController::class, 'index'])->name('services.index');
        Route::get('/my-applications',   [MyServiceApplicationsPageController::class,    'index'])->name('service-applications.index');
        Route::get('/job-applications',  [SeekerJobApplicationsPageController::class,    'index'])->name('job-applications.index');
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

    // API: Pakalpojumu pieteikumu pārvaldība (meistari pieņem/noraida)
    Route::middleware(['role:master'])->prefix('service-applications')->name('master.service-applications.')->group(function () {
        Route::patch('/{id}/accept', [MasterServiceApplicationController::class, 'accept'])->name('accept');
        Route::patch('/{id}/reject', [MasterServiceApplicationController::class, 'reject'])->name('reject');
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

    // API: Sūdzības
    Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');

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