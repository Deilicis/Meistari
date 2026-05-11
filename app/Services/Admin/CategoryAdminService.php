<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\DTOs\Categories\CreateCategoryDTO;
use App\DTOs\Categories\MergeCategoriesDTO;
use App\DTOs\Categories\UpdateCategoryDTO;
use App\Exceptions\Categories\CategoryMergeConflictException;
use App\Exceptions\Categories\CategoryNotDeletableException;
use App\Exceptions\Categories\SystemCategoryProtectedException;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryAdminService
{
    public function __construct(
        private readonly CategoryRepository $repo,
    ) {}

    public function getTreeWithStats(): array
    {
        $topLevel = $this->repo->getWithUsageCounts();

        return $topLevel->map(fn (Category $cat) => $this->serializeNode($cat, true))->values()->toArray();
    }

    public function create(CreateCategoryDTO $dto): Category
    {
        $slug = $this->generateUniqueSlug($dto->name);

        return $this->repo->create($dto, $slug);
    }

    public function update(int $id, UpdateCategoryDTO $dto): Category
    {
        $category = $this->loadOrFail($id);

        if ($category->isSystem() && $dto->parentId !== $category->getParentId()) {
            throw new SystemCategoryProtectedException('Sistēmas kategorijas vecākkategoriju nevar mainīt.');
        }

        $newSlug = null;
        if ($dto->regenerateSlug && !$category->isSystem()) {
            $newSlug = $this->generateUniqueSlug($dto->name, $id);
        }

        return $this->repo->update($id, $dto, $newSlug);
    }

    public function delete(int $id): void
    {
        $category = $this->loadOrFail($id);

        if ($category->isSystem()) {
            throw new CategoryNotDeletableException('Sistēmas kategoriju nevar dzēst.');
        }

        $services  = $this->repo->countActiveServices($id);
        $jobs      = $this->repo->countActiveJobRequests($id);
        $children  = $this->repo->countChildren($id);

        $reasons = [];
        if ($services > 0)  $reasons[] = "{$services} aktīvi pakalpojumi";
        if ($jobs > 0)      $reasons[] = "{$jobs} aktīvi darbi";
        if ($children > 0)  $reasons[] = "{$children} apakškategorijas";

        if (!empty($reasons)) {
            throw new CategoryNotDeletableException('Nevar dzēst: ' . implode(', ', $reasons) . '.');
        }

        $this->repo->softDelete($id);
    }

    public function merge(MergeCategoriesDTO $dto): Category
    {
        return DB::transaction(function () use ($dto): Category {
            $source = $this->loadOrFail($dto->sourceId);
            $target = $this->loadOrFail($dto->targetId);

            if ($source->isSystem()) {
                throw new SystemCategoryProtectedException('Sistēmas kategoriju nevar apvienot.');
            }

            if ($target->isSystem()) {
                throw new SystemCategoryProtectedException('Nevar apvienot kategorijas ar sistēmas kategoriju.');
            }

            if ($source->isTopLevel() !== $target->isTopLevel()) {
                throw new CategoryMergeConflictException('Var apvienot tikai viena līmeņa kategorijas.');
            }

            $sourceChildSlugs = $this->repo->getChildSlugs($dto->sourceId);
            $targetChildSlugs = $this->repo->getChildSlugs($dto->targetId);
            $conflicts = array_intersect($sourceChildSlugs, $targetChildSlugs);

            if (!empty($conflicts)) {
                throw new CategoryMergeConflictException(
                    'Apakškategoriju slug konflikts: ' . implode(', ', $conflicts) . '. Pārdēvē vispirms.'
                );
            }

            $this->repo->reparentChildren($dto->sourceId, $dto->targetId);
            $this->repo->moveServicesAndJobs($dto->sourceId, $dto->targetId);
            $this->repo->softDelete($dto->sourceId);

            return $target->fresh(['children']);
        });
    }

    // ─── Private helpers ─────────────────────────────────────────────────────────

    private function loadOrFail(int $id): Category
    {
        $cat = $this->repo->findById($id);

        if (!$cat) {
            abort(404, 'Kategorija nav atrasta.');
        }

        return $cat;
    }

    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name, '-', 'lv');
        $slug = $base;
        $i    = 2;

        while (!$this->repo->isSlugUnique($slug, $excludeId)) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    private function serializeNode(Category $cat, bool $withChildren = false): array
    {
        $servicesCount   = $cat->services_count   ?? 0;
        $jobsCount       = $cat->job_requests_count ?? 0;
        $childrenCount   = $cat->children ? $cat->children->count() : 0;

        $node = [
            'id'               => $cat->getId(),
            'name'             => $cat->getName(),
            'slug'             => $cat->getSlug(),
            'icon'             => $cat->getIcon(),
            'parent_id'        => $cat->getParentId(),
            'is_system'        => $cat->isSystem(),
            'services_count'   => $servicesCount,
            'job_requests_count' => $jobsCount,
            'children_count'   => $childrenCount,
            'can_delete'       => !$cat->isSystem() && $servicesCount === 0 && $jobsCount === 0 && $childrenCount === 0,
            'created_at'       => $cat->getCreatedAt()?->toISOString(),
        ];

        if ($withChildren && $cat->children) {
            $node['children'] = $cat->children->map(fn ($child) => $this->serializeNode($child, false))->values()->toArray();
        }

        return $node;
    }
}
