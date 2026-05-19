<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Job\JobStatusEnum;
use App\Enums\Role\RoleNameEnum;
use App\Models\Category;
use App\Models\JobRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\Repositories\Category\CategoryLogicRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    public function __construct(
        private readonly CategoryLogicRepository $categoryLogicRepo,
    ) {}

    public function index(Request $request): Response
    {
        $featuredMasters = User::whereHas('roles', fn ($q) => $q->where(Role::NAME, RoleNameEnum::MASTER->value))
            ->with('profile')
            ->withCount('reviewsReceived')
            ->withAvg('reviewsReceived', 'rating')
            ->orderByDesc('reviews_received_avg_rating')
            ->limit(4)
            ->get();

        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'stats' => [
                'masters' => User::whereHas('roles', fn ($q) => $q->where(Role::NAME, RoleNameEnum::MASTER->value))->count(),
                'completed_jobs' => JobRequest::where(JobRequest::STATUS, JobStatusEnum::COMPLETED->value)->count(),
                'categories' => Category::count(),
            ],
            'popularCategories' => $this->categoryLogicRepo->getPopularCategories(8),
            'featuredMasters' => $featuredMasters,
        ]);
    }
}
