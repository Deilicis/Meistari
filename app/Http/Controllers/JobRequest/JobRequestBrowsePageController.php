<?php

declare(strict_types=1);

namespace App\Http\Controllers\JobRequest;

use App\Http\Controllers\Controller;
use App\Services\Repositories\Application\ApplicationLogicRepository;
use App\Services\Repositories\Category\CategoryLogicRepository;
use App\Services\Repositories\JobRequest\JobRequestLogicRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class JobRequestBrowsePageController extends Controller
{
    public function __construct(
        private readonly JobRequestLogicRepository  $jobRequestLogicRepo,
        private readonly ApplicationLogicRepository $applicationLogicRepo,
        private readonly CategoryLogicRepository    $categoryLogicRepo,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'category_id', 'budget_min', 'budget_max']);

        return Inertia::render('Master/JobRequests/BrowseJobRequests', [
            'jobRequests' => $this->jobRequestLogicRepo->getPublicJobRequests($filters),
            'categories' => $this->categoryLogicRepo->getNestedCategories(),
            'filters' => $filters,
            'appliedJobIds' => $this->applicationLogicRepo->getAppliedJobIds(Auth::id()),
        ]);
    }
}
