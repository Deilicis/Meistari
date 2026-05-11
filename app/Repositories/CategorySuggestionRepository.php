<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTOs\CategorySuggestions\SubmitCategorySuggestionDTO;
use App\Enums\CategorySuggestionStatusEnum;
use App\Models\CategorySuggestion;
use App\Models\JobRequest;
use App\Models\Service;
use Carbon\Carbon;
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

    public function getPending(): Collection
    {
        return CategorySuggestion::where(CategorySuggestion::STATUS, CategorySuggestionStatusEnum::PENDING->value)
            ->with(['suggestedBy', 'parentCategory'])
            ->withCount(['services', 'jobRequests'])
            ->orderBy(CategorySuggestion::CREATED_AT)
            ->get();
    }

    public function getResolved(): Collection
    {
        return CategorySuggestion::whereIn(CategorySuggestion::STATUS, [
            CategorySuggestionStatusEnum::APPROVED->value,
            CategorySuggestionStatusEnum::REJECTED->value,
            CategorySuggestionStatusEnum::MERGED->value,
        ])
            ->with(['suggestedBy', 'parentCategory', 'reviewedBy', 'resultingCategory'])
            ->orderByDesc(CategorySuggestion::REVIEWED_AT)
            ->limit(100)
            ->get();
    }

    public function markApproved(int $id, int $reviewedByUserId, int $resultingCategoryId): void
    {
        CategorySuggestion::where(CategorySuggestion::ID, $id)->update([
            CategorySuggestion::STATUS               => CategorySuggestionStatusEnum::APPROVED->value,
            CategorySuggestion::REVIEWED_BY_USER_ID  => $reviewedByUserId,
            CategorySuggestion::REVIEWED_AT          => Carbon::now(),
            CategorySuggestion::RESULTING_CATEGORY_ID => $resultingCategoryId,
        ]);
    }

    public function markRejected(int $id, int $reviewedByUserId, string $reviewNote): void
    {
        CategorySuggestion::where(CategorySuggestion::ID, $id)->update([
            CategorySuggestion::STATUS              => CategorySuggestionStatusEnum::REJECTED->value,
            CategorySuggestion::REVIEWED_BY_USER_ID => $reviewedByUserId,
            CategorySuggestion::REVIEWED_AT         => Carbon::now(),
            CategorySuggestion::REVIEW_NOTE         => $reviewNote,
        ]);
    }

    public function markMerged(int $id, int $reviewedByUserId, int $resultingCategoryId): void
    {
        CategorySuggestion::where(CategorySuggestion::ID, $id)->update([
            CategorySuggestion::STATUS                => CategorySuggestionStatusEnum::MERGED->value,
            CategorySuggestion::REVIEWED_BY_USER_ID   => $reviewedByUserId,
            CategorySuggestion::REVIEWED_AT           => Carbon::now(),
            CategorySuggestion::RESULTING_CATEGORY_ID => $resultingCategoryId,
        ]);
    }

    public function migrateListingsToCategory(int $suggestionId, int $categoryId): array
    {
        $services = Service::where(Service::PENDING_CATEGORY_SUGGESTION_ID, $suggestionId)
            ->update([
                Service::CATEGORY_ID                      => $categoryId,
                Service::PENDING_CATEGORY_SUGGESTION_ID   => null,
            ]);

        $jobs = JobRequest::where(JobRequest::PENDING_CATEGORY_SUGGESTION_ID, $suggestionId)
            ->update([
                JobRequest::CATEGORY_ID                    => $categoryId,
                JobRequest::PENDING_CATEGORY_SUGGESTION_ID => null,
            ]);

        return ['services' => $services, 'job_requests' => $jobs];
    }
}
