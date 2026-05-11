<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTOs\Categories\CreateCategoryDTO;
use App\DTOs\Categories\UpdateCategoryDTO;
use App\Models\Category;
use App\Models\JobRequest;
use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository
{
    public function findById(int $id): ?Category
    {
        return Category::find($id);
    }

    public function findBySlug(string $slug): ?Category
    {
        return Category::where(Category::SLUG, $slug)->first();
    }

    public function getWithUsageCounts(): Collection
    {
        return Category::withCount(['services', 'jobRequests'])
            ->with(['children' => fn ($q) => $q->withCount(['services', 'jobRequests'])])
            ->whereNull(Category::PARENT_ID)
            ->orderBy(Category::NAME)
            ->get();
    }

    public function countActiveServices(int $categoryId): int
    {
        return Service::where(Service::CATEGORY_ID, $categoryId)->whereNull('deleted_at')->count();
    }

    public function countActiveJobRequests(int $categoryId): int
    {
        return JobRequest::where(JobRequest::CATEGORY_ID, $categoryId)->whereNull('deleted_at')->count();
    }

    public function countChildren(int $categoryId): int
    {
        return Category::where(Category::PARENT_ID, $categoryId)->count();
    }

    public function getDescendantIds(int $categoryId): array
    {
        return Category::where(Category::PARENT_ID, $categoryId)
            ->pluck(Category::ID)
            ->toArray();
    }

    public function create(CreateCategoryDTO $dto, string $slug): Category
    {
        return Category::create([
            Category::NAME      => $dto->name,
            Category::SLUG      => $slug,
            Category::ICON      => $dto->icon,
            Category::PARENT_ID => $dto->parentId,
        ]);
    }

    public function update(int $id, UpdateCategoryDTO $dto, ?string $newSlug): Category
    {
        $category = Category::findOrFail($id);

        $data = [
            Category::NAME      => $dto->name,
            Category::ICON      => $dto->icon,
            Category::PARENT_ID => $dto->parentId,
        ];

        if ($newSlug !== null) {
            $data[Category::SLUG] = $newSlug;
        }

        $category->update($data);

        return $category->fresh();
    }

    public function softDelete(int $id): void
    {
        Category::findOrFail($id)->delete();
    }

    public function reparentChildren(int $fromId, int $toId): int
    {
        return Category::where(Category::PARENT_ID, $fromId)
            ->update([Category::PARENT_ID => $toId]);
    }

    public function moveServicesAndJobs(int $fromId, int $toId): array
    {
        $services = Service::where(Service::CATEGORY_ID, $fromId)
            ->update([Service::CATEGORY_ID => $toId]);

        $jobs = JobRequest::where(JobRequest::CATEGORY_ID, $fromId)
            ->update([JobRequest::CATEGORY_ID => $toId]);

        return ['services' => $services, 'job_requests' => $jobs];
    }

    public function isSlugUnique(string $slug, ?int $excludeId = null): bool
    {
        $query = Category::where(Category::SLUG, $slug);

        if ($excludeId !== null) {
            $query->where(Category::ID, '!=', $excludeId);
        }

        return !$query->exists();
    }

    public function getChildSlugs(int $categoryId): array
    {
        return Category::where(Category::PARENT_ID, $categoryId)
            ->pluck(Category::SLUG)
            ->toArray();
    }
}
