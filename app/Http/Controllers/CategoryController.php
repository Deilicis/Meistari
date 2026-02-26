<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Category\SaveCategoryRequest;
use App\Models\Category;
use App\Services\Repositories\Category\CategoryLogicRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
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

    public function apiIndex(): JsonResponse
    {
        return response()->json($this->logicRepository->getNestedCategories());
    }

    public function store(SaveCategoryRequest $request): RedirectResponse
    {
        $this->logicRepository->createCategory($request->toDTO());

        return back()->with('success', 'Kategorija veiksmīgi izveidota!');
    }

    public function update(SaveCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->logicRepository->updateCategory($category, $request->toDTO());

        return back()->with('success', 'Kategorija atjaunināta!');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->logicRepository->deleteCategory($category);

        return back()->with('success', 'Kategorija izdzēsta!');
    }
}