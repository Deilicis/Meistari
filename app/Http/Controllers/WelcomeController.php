<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Repositories\Category\CategoryLogicRepository;
use App\Services\Repositories\JobRequest\JobRequestLogicRepository;
use App\Services\Repositories\Service\ServiceLogicRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    public function __construct(
        private readonly ServiceLogicRepository    $serviceLogicRepo,
        private readonly JobRequestLogicRepository $jobRequestLogicRepo,
        private readonly CategoryLogicRepository   $categoryLogicRepo,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'category_id', 'tab']);

        return Inertia::render('Welcome', [
            'canLogin'    => Route::has('login'),
            'canRegister' => Route::has('register'),
            'services'    => $this->serviceLogicRepo->getPublicServices($filters, 8)->items(),
            'jobRequests' => $this->jobRequestLogicRepo->getPublicJobRequests($filters, 8)->items(),
            'popularCategories' => $this->categoryLogicRepo->getPopularCategories(5),
            'filters'     => $filters,
        ]);
    }
}
