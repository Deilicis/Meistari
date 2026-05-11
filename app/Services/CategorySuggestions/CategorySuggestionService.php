<?php

declare(strict_types=1);

namespace App\Services\CategorySuggestions;

use App\DTOs\CategorySuggestions\SubmitCategorySuggestionDTO;
use App\DTOs\Notifications\CreateNotificationDTO;
use App\Enums\NotificationTypeEnum;
use App\Enums\Role\RoleNameEnum;
use App\Models\CategorySuggestion;
use App\Models\Role;
use App\Models\User;
use App\Repositories\CategorySuggestionRepository;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Collection;

class CategorySuggestionService
{
    public function __construct(
        private readonly CategorySuggestionRepository $repository,
        private readonly NotificationService          $notificationService,
    ) {}

    public function submit(SubmitCategorySuggestionDTO $dto): CategorySuggestion
    {
        $existing = $this->repository->findPendingByName($dto->name, $dto->parentCategoryId);

        if ($existing !== null) {
            return $existing;
        }

        $suggestion = $this->repository->create($dto);

        $this->notifyAdmins($suggestion);

        return $suggestion;
    }

    public function getSimilarForSearch(string $searchTerm, ?int $parentId): Collection
    {
        return $this->repository->getSimilarPending($searchTerm, $parentId);
    }

    private function notifyAdmins(CategorySuggestion $suggestion): void
    {
        $admins = User::whereHas('roles', fn ($q) => $q->where(Role::NAME, RoleNameEnum::ADMIN->value))->get();

        foreach ($admins as $admin) {
            $this->notificationService->create(new CreateNotificationDTO(
                userId: $admin->getId(),
                type: NotificationTypeEnum::NEW_CATEGORY_SUGGESTION,
                title: 'Jauns kategorijas priekšlikums',
                body: '"' . $suggestion->getName() . '" — pārskatīt priekšlikumu.',
                actionUrl: url('/admin/category-suggestions/' . $suggestion->getId()),
                metadata: ['suggestion_id' => $suggestion->getId()],
            ));
        }
    }
}
