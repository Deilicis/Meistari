<?php

declare(strict_types=1);

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Services\Repositories\Category\CategoryLogicRepository;
use App\Services\Repositories\Service\ServiceLogicRepository;
use App\Services\Repositories\ServiceApplication\ServiceApplicationLogicRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ServiceBrowsePageController extends Controller
{
    public function __construct(
        private readonly ServiceLogicRepository $serviceLogicRepo,
        private readonly ServiceApplicationLogicRepository $applicationLogicRepo,
        private readonly CategoryLogicRepository $categoryLogicRepo
    ) {
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'category_id', 'price_min', 'price_max']);

        return Inertia::render('Seeker/Services/BrowseServices', [
            'services'          => $this->serviceLogicRepo->getPublicServices($filters),
            'categories'        => $this->categoryLogicRepo->getFlatCategories(),
            'filters'           => $filters,
            'appliedServiceIds' => $this->applicationLogicRepo->getAppliedServiceIds(Auth::id()),
        ]);
    }
}
