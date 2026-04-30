<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTOs\Notifications\CreateNotificationDTO;
use App\Models\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class NotificationRepository
{
    public function create(CreateNotificationDTO $dto): Notification
    {
        return Notification::create([
            Notification::USER_ID    => $dto->userId,
            Notification::TYPE       => $dto->type->value,
            Notification::TITLE      => $dto->title,
            Notification::BODY       => $dto->body,
            Notification::ACTION_URL => $dto->actionUrl,
            Notification::METADATA   => $dto->metadata,
        ]);
    }

    public function findByIdForUser(int $id, int $userId): ?Notification
    {
        return Notification::where(Notification::ID, $id)
            ->where(Notification::USER_ID, $userId)
            ->first();
    }

    public function getRecentForUser(int $userId, int $limit = 10): Collection
    {
        return Notification::where(Notification::USER_ID, $userId)
            ->latest(Notification::CREATED_AT)
            ->limit($limit)
            ->get();
    }

    public function getAllForUser(int $userId): Collection
    {
        return Notification::where(Notification::USER_ID, $userId)
            ->latest(Notification::CREATED_AT)
            ->get();
    }

    public function getUnreadCountForUser(int $userId): int
    {
        return Notification::where(Notification::USER_ID, $userId)
            ->whereNull(Notification::READ_AT)
            ->count();
    }

    public function markAsRead(int $id, int $userId): void
    {
        Notification::where(Notification::ID, $id)
            ->where(Notification::USER_ID, $userId)
            ->whereNull(Notification::READ_AT)
            ->update([Notification::READ_AT => Carbon::now()]);
    }

    public function markAllAsReadForUser(int $userId): void
    {
        Notification::where(Notification::USER_ID, $userId)
            ->whereNull(Notification::READ_AT)
            ->update([Notification::READ_AT => Carbon::now()]);
    }

    public function delete(int $id, int $userId): void
    {
        Notification::where(Notification::ID, $id)
            ->where(Notification::USER_ID, $userId)
            ->delete();
    }
}
