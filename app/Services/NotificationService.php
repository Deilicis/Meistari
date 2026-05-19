<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Notifications\CreateNotificationDTO;
use App\Events\NotificationCreated;
use App\Models\Notification;
use App\Repositories\NotificationRepository;
use Illuminate\Support\Collection;

class NotificationService
{
    public function __construct(
        private readonly NotificationRepository $repository,
    ) {}

    public function create(CreateNotificationDTO $dto): Notification
    {
        $notification = $this->repository->create($dto);

        try {
            NotificationCreated::dispatch($notification);
        } catch (\Throwable) {

        }

        return $notification;
    }

    public function getRecentForUser(int $userId): Collection
    {
        return $this->repository->getRecentForUser($userId);
    }

    public function getUnreadCountForUser(int $userId): int
    {
        return $this->repository->getUnreadCountForUser($userId);
    }

    public function markAsRead(int $id, int $userId): void
    {
        $this->repository->markAsRead($id, $userId);
    }

    public function markAllAsRead(int $userId): void
    {
        $this->repository->markAllAsReadForUser($userId);
    }

    public function delete(int $id, int $userId): void
    {
        $this->repository->delete($id, $userId);
    }
}
