<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MergeCategoryRequest;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Services\Admin\CategoryAdminService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryAdminService $service,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Categories/Index', [
            'categories' => $this->service->getTreeWithStats(),
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->service->create($request->toDTO());

        return response()->json($category, 201);
    }

    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $category = $this->service->update($id, $request->toDTO());

        return response()->json($category);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(['message' => 'Kategorija dzēsta.']);
    }

    public function merge(MergeCategoryRequest $request): JsonResponse
    {
        $target = $this->service->merge($request->toDTO());

        return response()->json($target);
    }
}
