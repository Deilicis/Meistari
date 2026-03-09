<?php

declare(strict_types=1);

namespace App\Http\Controllers\JobRequest;

use App\Http\Controllers\Controller;
use App\Models\JobRequest;
use App\Services\Repositories\Category\CategoryLogicRepository;
use App\Services\Repositories\JobRequest\JobRequestLogicRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class JobRequestPageController extends Controller
{
    public function __construct(
        private readonly JobRequestLogicRepository $jobRequestRepository,
        private readonly CategoryLogicRepository $categoryRepository
    ) {
    }

    public function index(Request $request): Response
    {
        $userId = Auth::id();

        $filters = $request->only(['search', 'category_id', 'status', 'budget_min', 'budget_max']);

        $jobRequests = $this->jobRequestRepository->getUserJobRequests($userId, $filters);

        $categories = $this->categoryRepository->getFlatCategories();

        return Inertia::render('Seeker/JobRequests/MyJobRequests', [
            'jobRequests' => $jobRequests,
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    public function show(JobRequest $jobRequest): Response
    {
        $jobRequest->load(['category', 'applications.user.profile']);

        return Inertia::render('Seeker/JobRequests/Show', [
            'jobRequest' => $jobRequest,
        ]);
    }
}