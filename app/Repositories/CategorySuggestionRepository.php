<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTOs\CategorySuggestions\SubmitCategorySuggestionDTO;
use App\Enums\CategorySuggestionStatusEnum;
use App\Models\CategorySuggestion;
use Illuminate\Database\Eloquent\Collection;

class CategorySuggestionRepository
{
    public function create(SubmitCategorySuggestionDTO $dto): CategorySuggestion
    {
        return CategorySuggestion::create([
            CategorySuggestion::SUGGESTED_BY_USER_ID => $dto->suggestedByUserId,
            CategorySuggestion::NAME                 => $dto->name,
            CategorySuggestion::PARENT_CATEGORY_ID   => $dto->parentCategoryId,
            CategorySuggestion::NOTE                 => $dto->note,
            CategorySuggestion::STATUS               => CategorySuggestionStatusEnum::PENDING->value,
        ]);
    }

    public function findById(int $id): ?CategorySuggestion
    {
        return CategorySuggestion::find($id);
    }

    public function findPendingByName(string $name, ?int $parentId): ?CategorySuggestion
    {
        return CategorySuggestion::where(CategorySuggestion::STATUS, CategorySuggestionStatusEnum::PENDING->value)
            ->whereRaw('LOWER(' . CategorySuggestion::NAME . ') = LOWER(?)', [$name])
            ->where(CategorySuggestion::PARENT_CATEGORY_ID, $parentId)
            ->first();
    }

    public function getSimilarPending(string $searchTerm, ?int $parentId, int $limit = 3): Collection
    {
        return CategorySuggestion::where(CategorySuggestion::STATUS, CategorySuggestionStatusEnum::PENDING->value)
            ->whereRaw('LOWER(' . CategorySuggestion::NAME . ') LIKE LOWER(?)', ['%' . $searchTerm . '%'])
            ->where(CategorySuggestion::PARENT_CATEGORY_ID, $parentId)
            ->with('suggestedBy')
            ->orderBy(CategorySuggestion::CREATED_AT)
            ->limit($limit)
            ->get();
    }
}
