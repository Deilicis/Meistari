<?php

declare(strict_types=1);

namespace App\Services\Repositories\Category;

use App\Constants\ErrorMessages;
use App\DataTransferObjects\Category\SaveCategoryRequestData;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class CategoryLogicRepository
{
    public function __construct(
        private readonly CategoryDbRepository $dbRepository
    ) {
    }

    public function getNestedCategories(): Collection
    {
        return $this->dbRepository->getAllNested();
    }

    public function getFlatCategories(): Collection
    {
        return $this->dbRepository->getAllFlat();
    }

    public function getFlatCategoriesWithServiceCount(): Collection
    {
        return $this->dbRepository->getAllFlatWithServiceCount();
    }

    public function getFlatCategoriesWithJobRequestCount(): Collection
    {
        return $this->dbRepository->getAllFlatWithJobRequestCount();
    }

    public function getPopularCategories(int $limit = 5): Collection
    {
        return $this->dbRepository->getPopular($limit);
    }

    public function getCategoryById(int $id): Category
    {
        return $this->dbRepository->getById($id);
    }

    public function createCategory(SaveCategoryRequestData $dto): Category
    {
        return $this->dbRepository->create($dto->toArray());
    }

    public function updateCategory(Category $category, SaveCategoryRequestData $dto): bool
    {
        if ($dto->parentId === $category->getId()) {
            throw ValidationException::withMessages([
                Category::PARENT_ID => ErrorMessages::CATEGORY_SELF_PARENT,
            ]);
        }

        return $this->dbRepository->update($category, $dto->toArray());
    }

    public function deleteCategory(Category $category): ?bool
    {
        if ($category->children()->exists()) {
            throw ValidationException::withMessages([
                'error' => ErrorMessages::CATEGORY_HAS_CHILDREN,
            ]);
        }

        if ($category->jobRequests()->exists()) {
            throw ValidationException::withMessages([
                'error' => ErrorMessages::CATEGORY_HAS_JOB_REQUESTS,
            ]);
        }

        return $this->dbRepository->delete($category);
    }
}
