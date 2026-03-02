<?php

declare(strict_types=1);

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Services\Repositories\Category\CategoryLogicRepository;
use App\Services\Repositories\Service\ServiceLogicRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServicePageController extends Controller
{
    public function __construct(
        private readonly ServiceLogicRepository $serviceLogicRepo,
        private readonly CategoryLogicRepository $categoryLogicRepo
    ) {
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'category_id', 'is_active', 'price_min', 'price_max']);

        $services = $this->serviceLogicRepo->getUserServices($request->user()->id, $filters);
        
        return Inertia::render('Master/Services/MyServices', [
            'services' => ServiceResource::collection($services),
            'categories' => $this->categoryLogicRepo->getFlatCategories(),
            'filters' => $filters,
        ]);
    }
}