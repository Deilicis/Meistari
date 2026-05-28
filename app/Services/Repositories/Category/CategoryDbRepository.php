<?php

declare(strict_types=1);

namespace App\Services\Repositories\Category;

use App\Enums\Job\JobStatusEnum;
use App\Models\Category;
use App\Models\JobRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CategoryDbRepository
{
    public function getAllNested(): Collection
    {
        return Category::with('children')
            ->whereNull(Category::PARENT_ID)
            ->orderBy(Category::NAME)
            ->get();
    }

    public function getAllFlat(): Collection
    {
        return Category::orderBy(Category::NAME)->get();
    }

    public function getAllFlatWithServiceCount(): Collection
    {
        return Category::withCount('services')
            ->orderBy(Category::NAME)
            ->get();
    }

    public function getAllFlatWithJobRequestCount(): Collection
    {
        return Category::withCount(['jobRequests' => fn($q) => $q->where(JobRequest::STATUS, JobStatusEnum::OPEN->value)])
            ->orderBy(Category::NAME)
            ->get();
    }

    public function getPopular(int $limit = 5): Collection
    {
        $openStatus = JobStatusEnum::OPEN->value;

        return Category::whereNull(Category::PARENT_ID)
            ->addSelect([
                'categories.*',
                DB::raw("(
                    SELECT COUNT(*)
                    FROM services
                    WHERE services.deleted_at IS NULL
                    AND (
                        services.category_id = categories.id
                        OR services.category_id IN (
                            SELECT id FROM categories AS ch
                            WHERE ch.parent_id = categories.id
                            AND ch.deleted_at IS NULL
                        )
                    )
                ) AS services_count"),
                DB::raw("(
                    SELECT COUNT(*)
                    FROM job_requests
                    WHERE job_requests.deleted_at IS NULL
                    AND job_requests.status = '{$openStatus}'
                    AND (
                        job_requests.category_id = categories.id
                        OR job_requests.category_id IN (
                            SELECT id FROM categories AS ch
                            WHERE ch.parent_id = categories.id
                            AND ch.deleted_at IS NULL
                        )
                    )
                ) AS job_requests_count"),
            ])
            ->orderByRaw('(services_count + job_requests_count) DESC')
            ->limit($limit)
            ->get();
    }

    public function getNestedWithServiceCount(): Collection
    {
        return Category::withCount('services')
            ->whereNull(Category::PARENT_ID)
            ->with(['children' => fn ($q) => $q->withCount('services')])
            ->orderBy(Category::IS_SYSTEM)
            ->orderBy(Category::NAME)
            ->get();
    }

    public function getNestedWithJobRequestCount(): Collection
    {
        $activeOnly = fn ($q) => $q->where(JobRequest::STATUS, JobStatusEnum::OPEN->value);

        return Category::withCount(['jobRequests' => $activeOnly])
            ->whereNull(Category::PARENT_ID)
            ->with(['children' => fn ($q) => $q->withCount(['jobRequests' => fn ($q) => $q->where(JobRequest::STATUS, JobStatusEnum::OPEN->value)])])
            ->orderBy(Category::NAME)
            ->get();
    }

    public function getCategoryAndChildrenIds(int $categoryId): array
    {
        $childIds = Category::where(Category::PARENT_ID, $categoryId)
            ->pluck(Category::ID)
            ->toArray();

        return array_merge([$categoryId], $childIds);
    }

    public function getById(int $id): Category
    {
        return Category::findOrFail($id);
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): bool
    {
        return $category->update($data);
    }

    public function delete(Category $category): ?bool
    {
        return $category->delete();
    }
}