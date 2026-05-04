<?php

declare(strict_types=1);

namespace App\Services\Repositories\ServiceApplication;

use App\Enums\Job\ApplicationStatusEnum;
use App\Models\ServiceApplication;
use Illuminate\Support\Collection;

class ServiceApplicationDbRepository
{
    public function create(array $data): ServiceApplication
    {
        return ServiceApplication::create($data);
    }

    public function getAppliedServiceIds(int $userId): array
    {
        return ServiceApplication::where(ServiceApplication::USER_ID, $userId)
            ->whereNotIn(ServiceApplication::STATUS, [ApplicationStatusEnum::CANCELLED->value])
            ->pluck(ServiceApplication::SERVICE_ID)
            ->toArray();
    }

    public function getByUserIdWithRelations(int $userId): Collection
    {
        return ServiceApplication::where(ServiceApplication::USER_ID, $userId)
            ->with(['service.user.profile', 'service.category'])
            ->orderByDesc(ServiceApplication::CREATED_AT)
            ->get();
    }

    public function findByIdForUser(int $id, int $userId): ?ServiceApplication
    {
        return ServiceApplication::where(ServiceApplication::ID, $id)
            ->where(ServiceApplication::USER_ID, $userId)
            ->first();
    }

    public function findByServiceAndUser(int $serviceId, int $userId): ?ServiceApplication
    {
        return ServiceApplication::where(ServiceApplication::SERVICE_ID, $serviceId)
            ->where(ServiceApplication::USER_ID, $userId)
            ->first();
    }

    public function cancel(ServiceApplication $application): ServiceApplication
    {
        $application->update([ServiceApplication::STATUS => ApplicationStatusEnum::CANCELLED->value]);
        return $application;
    }

    public function getForMaster(int $masterId): Collection
    {
        return ServiceApplication::whereHas('service', fn ($q) => $q->where('user_id', $masterId))
            ->with(['service', 'user.profile'])
            ->orderByDesc(ServiceApplication::CREATED_AT)
            ->get();
    }

    public function getForService(int $serviceId): Collection
    {
        return ServiceApplication::where(ServiceApplication::SERVICE_ID, $serviceId)
            ->with(['service', 'user.profile'])
            ->orderByDesc(ServiceApplication::CREATED_AT)
            ->get();
    }

    public function findByIdForMaster(int $id, int $masterId): ?ServiceApplication
    {
        return ServiceApplication::where(ServiceApplication::ID, $id)
            ->whereHas('service', fn ($q) => $q->where('user_id', $masterId))
            ->with(['service', 'user.profile'])
            ->first();
    }

    public function updateStatus(ServiceApplication $application, ApplicationStatusEnum $status): ServiceApplication
    {
        $application->update([ServiceApplication::STATUS => $status->value]);
        return $application->fresh();
    }
}
