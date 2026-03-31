<?php

declare(strict_types=1);

namespace App\Http\Controllers\Category;

use App\Http\Requests\Category\CreateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Http\Controllers\Controller;
use App\Services\Repositories\Category\CategoryLogicRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    private const MSG_CREATED = 'Kategorija veiksmīgi izveidota!';
    private const MSG_UPDATED = 'Kategorija atjaunināta!';
    private const MSG_DELETED = 'Kategorija izdzēsta!';
    private const FLASH_SUCCESS = 'success';
    private const KEY_MESSAGE = 'message';
    private const KEY_DATA = 'data';

    public function __construct(
        private readonly CategoryLogicRepository $logicRepository
    ) {
    }

    public function index(): Response
    {
        return Inertia::render('Admin/Categories/Index', [
            'categories' => $this->logicRepository->getNestedCategories(),
            'flatCategories' => $this->logicRepository->getFlatCategories(),
        ]);
    }

    public function store(CreateCategoryRequest $request): RedirectResponse
    {
        $this->logicRepository->createCategory($request->toDTO());
        return back()->with(self::FLASH_SUCCESS, self::MSG_CREATED);
    }

    public function update(CreateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->logicRepository->updateCategory($category, $request->toDTO());
        return back()->with(self::FLASH_SUCCESS, self::MSG_UPDATED);
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->logicRepository->deleteCategory($category);
        return back()->with(self::FLASH_SUCCESS, self::MSG_DELETED);
    }

    public function apiIndex(): JsonResponse
    {
        $categories = $this->logicRepository->getNestedCategories();

        return response()->json(CategoryResource::collection($categories));
    }

    public function apiShow(int $id): JsonResponse
    {
        $category = $this->logicRepository->getCategoryById($id);

        return response()->json(new CategoryResource($category));
    }

    public function apiStore(CreateCategoryRequest $request): JsonResponse
    {
        $category = $this->logicRepository->createCategory($request->toDTO());

        return response()->json([
            self::KEY_MESSAGE => self::MSG_CREATED,
            self::KEY_DATA => new CategoryResource($category),
        ], 201);
    }

    public function apiUpdate(CreateCategoryRequest $request, Category $category): JsonResponse
    {
        $this->logicRepository->updateCategory($category, $request->toDTO());

        $category->refresh();

        return response()->json([
            self::KEY_MESSAGE => self::MSG_UPDATED,
            self::KEY_DATA => new CategoryResource($category),
        ], 200);
    }

    public function apiDestroy(Category $category): JsonResponse
    {
        $this->logicRepository->deleteCategory($category);

        return response()->json([
            self::KEY_MESSAGE => self::MSG_DELETED,
        ], 200);
    }
}
