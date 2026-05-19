<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CategorySuggestion\SubmitCategorySuggestionRequest;
use App\Http\Resources\CategorySuggestionResource;
use App\Models\Category;
use App\Services\CategorySuggestions\CategorySuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategorySuggestionController extends Controller
{
    public function __construct(
        private readonly CategorySuggestionService $service,
    ) {}

    public function store(SubmitCategorySuggestionRequest $request): JsonResponse
    {
        $suggestion = $this->service->submit($request->toDTO());

        return response()->json(new CategorySuggestionResource($suggestion), 201);
    }

    public function search(Request $request): JsonResponse
    {
        $q = (string) $request->get('q', '');
        $parentId = $request->filled('parent_id') ? (int) $request->get('parent_id') : null;

        if (mb_strlen($q) < 1) {
            return response()->json(['approved' => [], 'pending' => []]);
        }

        $approved = Category::where(Category::NAME, 'like', '%' . $q . '%')
            ->where(Category::IS_SYSTEM, false)
            ->when($parentId !== null, fn ($query) => $query->where(Category::PARENT_ID, $parentId))
            ->when($parentId === null, fn ($query) => $query->whereNull(Category::PARENT_ID))
            ->limit(5)
            ->get();

        $pending = $this->service->getSimilarForSearch($q, $parentId);

        return response()->json([
            'approved' => $approved->map(fn ($c) => [
                'id' => $c->getId(),
                'name' => $c->getName(),
                'slug' => $c->getSlug(),
                'parent_id' => $c->getParentId(),
            ]),
            'pending' => $pending->map(fn ($s) => [
                'id' => $s->getId(),
                'name' => $s->getName(),
                'suggested_by_name' => $s->suggestedBy?->getName() ?? 'Cits lietotājs',
                'created_at' => $s->getCreatedAt()?->toISOString(),
            ]),
        ]);
    }
}
