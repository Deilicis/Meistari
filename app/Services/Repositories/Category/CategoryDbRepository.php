<?php

declare(strict_types=1);

namespace App\Services\Repositories\Category;

use App\Enums\Job\JobStatusEnum;
use App\Models\Category;
use App\Models\JobRequest;
use Illuminate\Database\Eloquent\Collection;

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
        return Category::withCount(['jobRequests' => fn($q) => $q->where(JobRequest::STATUS, JobStatusEnum::ACTIVE->value)])
            ->orderBy(Category::NAME)
            ->get();
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