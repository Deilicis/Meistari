<?php

declare(strict_types=1);

namespace App\Http\Controllers\JobRequest;

use App\Http\Controllers\Controller;
use App\Services\Repositories\Category\CategoryLogicRepository;
use Inertia\Inertia;
use Inertia\Response;

class JobRequestCategoryBrowsePageController extends Controller
{
    public function __construct(
        private readonly CategoryLogicRepository $categoryRepository
    ) {}

    public function index(): Response
    {
        return Inertia::render('Master/JobRequests/BrowseJobCategories', [
            'categories' => $this->categoryRepository->getNestedCategoriesWithJobRequestCount(),
        ]);
    }
}
