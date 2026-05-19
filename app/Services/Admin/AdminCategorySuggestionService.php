<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\DTOs\Categories\CreateCategoryDTO;
use App\DTOs\CategorySuggestions\ApproveSuggestionDTO;
use App\DTOs\CategorySuggestions\MergeSuggestionDTO;
use App\DTOs\CategorySuggestions\RejectSuggestionDTO;
use App\DTOs\Notifications\CreateNotificationDTO;
use App\Enums\NotificationTypeEnum;
use App\Models\CategorySuggestion;
use App\Repositories\CategoryRepository;
use App\Repositories\CategorySuggestionRepository;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminCategorySuggestionService
{
    public function __construct(
        private readonly CategorySuggestionRepository $suggestionRepo,
        private readonly CategoryRepository           $categoryRepo,
        private readonly NotificationService          $notificationService,
    ) {}

    public function getPendingList(): array
    {
        return $this->suggestionRepo->getPending()
            ->map(fn (CategorySuggestion $s) => $this->serialize($s))
            ->values()
            ->toArray();
    }

    public function getResolvedList(): array
    {
        return $this->suggestionRepo->getResolved()
            ->map(fn (CategorySuggestion $s) => $this->serialize($s))
            ->values()
            ->toArray();
    }

    public function approve(ApproveSuggestionDTO $dto): CategorySuggestion
    {
        $suggestion = $this->loadPendingOrFail($dto->suggestionId);

        return DB::transaction(function () use ($dto, $suggestion): CategorySuggestion {
            $slug = $this->generateUniqueSlug($suggestion->getName());
            $category = $this->categoryRepo->create(new CreateCategoryDTO(
                name:     $suggestion->getName(),
                parentId: $suggestion->getParentCategoryId(),
                icon:     $dto->icon,
            ), $slug);

            $this->suggestionRepo->migrateListingsToCategory($suggestion->getId(), $category->getId());
            $this->suggestionRepo->markApproved($suggestion->getId(), $dto->reviewedByUserId, $category->getId());

            $this->notificationService->create(new CreateNotificationDTO(
                userId:    $suggestion->getSuggestedByUserId(),
                type:      NotificationTypeEnum::CATEGORY_SUGGESTION_APPROVED,
                title:     'Kategorijas priekšlikums apstiprināts',
                body:      '"' . $suggestion->getName() . '" ir pievienota kā jauna kategorija.',
                actionUrl: null,
                metadata:  ['suggestion_id' => $suggestion->getId(), 'category_id' => $category->getId()],
            ));

            return $suggestion->fresh();
        });
    }

    public function reject(RejectSuggestionDTO $dto): CategorySuggestion
    {
        $suggestion = $this->loadPendingOrFail($dto->suggestionId);

        $this->suggestionRepo->markRejected($suggestion->getId(), $dto->reviewedByUserId, $dto->reviewNote);

        $this->notificationService->create(new CreateNotificationDTO(
            userId:    $suggestion->getSuggestedByUserId(),
            type:      NotificationTypeEnum::CATEGORY_SUGGESTION_REJECTED,
            title:     'Kategorijas priekšlikums noraidīts',
            body:      '"' . $suggestion->getName() . '": ' . $dto->reviewNote,
            actionUrl: null,
            metadata:  ['suggestion_id' => $suggestion->getId()],
        ));

        return $suggestion->fresh();
    }

    public function merge(MergeSuggestionDTO $dto): CategorySuggestion
    {
        $suggestion = $this->loadPendingOrFail($dto->suggestionId);
        $target = $this->categoryRepo->findById($dto->targetCategoryId);

        if ($target === null) {
            abort(422, 'Mērķa kategorija nav atrasta.');
        }

        return DB::transaction(function () use ($dto, $suggestion, $target): CategorySuggestion {
            $this->suggestionRepo->migrateListingsToCategory($suggestion->getId(), $target->getId());
            $this->suggestionRepo->markMerged($suggestion->getId(), $dto->reviewedByUserId, $target->getId());

            $this->notificationService->create(new CreateNotificationDTO(
                userId:    $suggestion->getSuggestedByUserId(),
                type:      NotificationTypeEnum::CATEGORY_SUGGESTION_MERGED,
                title:     'Kategorijas priekšlikums apvienots',
                body:      '"' . $suggestion->getName() . '" tika apvienots ar esošo kategoriju "' . $target->getName() . '".',
                actionUrl: null,
                metadata:  ['suggestion_id' => $suggestion->getId(), 'category_id' => $target->getId()],
            ));

            return $suggestion->fresh();
        });
    }


    private function loadPendingOrFail(int $id): CategorySuggestion
    {
        $s = $this->suggestionRepo->findById($id);

        if ($s === null) {
            abort(404, 'Priekšlikums nav atrasts.');
        }

        if (!$s->isPending()) {
            abort(409, 'Priekšlikums jau ir izskatīts.');
        }

        return $s;
    }

    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name, '-', 'lv');
        $slug = $base;
        $i = 2;

        while (!$this->categoryRepo->isSlugUnique($slug)) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    private function serialize(CategorySuggestion $s): array
    {
        return [
            'id' => $s->getId(),
            'name' => $s->getName(),
            'note' => $s->getNote(),
            'status' => $s->getStatus()->value,
            'status_label' => $s->getStatus()->label(),
            'parent_category' => $s->parentCategory ? [
                'id' => $s->parentCategory->getId(),
                'name' => $s->parentCategory->getName(),
            ] : null,
            'suggested_by' => $s->suggestedBy ? [
                'id' => $s->suggestedBy->getId(),
                'name' => $s->suggestedBy->getName(),
            ] : null,
            'reviewed_by' => $s->reviewedBy ? [
                'id' => $s->reviewedBy->getId(),
                'name' => $s->reviewedBy->getName(),
            ] : null,
            'review_note' => $s->getReviewNote(),
            'resulting_category' => $s->resultingCategory ? [
                'id' => $s->resultingCategory->getId(),
                'name' => $s->resultingCategory->getName(),
            ] : null,
            'services_count' => $s->services_count ?? 0,
            'job_requests_count' => $s->job_requests_count ?? 0,
            'created_at' => $s->getCreatedAt()?->toISOString(),
            'reviewed_at' => $s->getReviewedAt()?->toISOString(),
        ];
    }
}
