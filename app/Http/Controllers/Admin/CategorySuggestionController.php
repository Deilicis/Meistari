<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveSuggestionRequest;
use App\Http\Requests\Admin\MergeSuggestionRequest;
use App\Http\Requests\Admin\RejectSuggestionRequest;
use App\Repositories\CategoryRepository;
use App\Services\Admin\AdminCategorySuggestionService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategorySuggestionController extends Controller
{
    public function __construct(
        private readonly AdminCategorySuggestionService $service,
        private readonly CategoryRepository             $categoryRepo,
    ) {}

    public function index(): Response
    {
        $categories = $this->categoryRepo->getWithUsageCounts();

        return Inertia::render('Admin/CategorySuggestions/Index', [
            'pending' => $this->service->getPendingList(),
            'resolved' => $this->service->getResolvedList(),
            'categories' => $categories->map(fn ($c) => [
                'id' => $c->getId(),
                'name' => $c->getName(),
                'children' => $c->children?->map(fn ($ch) => [
                    'id' => $ch->getId(),
                    'name' => $ch->getName(),
                ])->values()->toArray() ?? [],
            ])->values()->toArray(),
        ]);
    }

    public function approve(ApproveSuggestionRequest $request, int $id): JsonResponse
    {
        $this->service->approve($request->toDTO($id));

        return response()->json(['message' => 'Priekšlikums apstiprināts.']);
    }

    public function reject(RejectSuggestionRequest $request, int $id): JsonResponse
    {
        $this->service->reject($request->toDTO($id));

        return response()->json(['message' => 'Priekšlikums noraidīts.']);
    }

    public function merge(MergeSuggestionRequest $request, int $id): JsonResponse
    {
        $this->service->merge($request->toDTO($id));

        return response()->json(['message' => 'Priekšlikums apvienots.']);
    }
}
