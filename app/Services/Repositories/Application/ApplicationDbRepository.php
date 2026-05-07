<?php

declare(strict_types=1);

namespace App\Services\Repositories\Application;

use App\Enums\Job\ApplicationStatusEnum;
use App\Models\Application;
use Illuminate\Support\Collection;

class ApplicationDbRepository
{
    public function create(array $data): Application
    {
        return Application::create($data);
    }

    public function getAppliedJobIds(int $userId): array
    {
        return Application::where(Application::USER_ID, $userId)
            ->whereNotIn(Application::STATUS, [ApplicationStatusEnum::CANCELLED->value])
            ->pluck(Application::JOB_REQUEST_ID)
            ->toArray();
    }

    public function getByUserIdWithRelations(int $userId): Collection
    {
        return Application::where(Application::USER_ID, $userId)
            ->with(['jobRequest.category', 'jobRequest.user.profile'])
            ->orderByDesc(Application::CREATED_AT)
            ->get();
    }

    public function findByIdForUser(int $id, int $userId): ?Application
    {
        return Application::where(Application::ID, $id)
            ->where(Application::USER_ID, $userId)
            ->first();
    }

    public function findByJobRequestAndUser(int $jobRequestId, int $userId): ?Application
    {
        return Application::where(Application::JOB_REQUEST_ID, $jobRequestId)
            ->where(Application::USER_ID, $userId)
            ->where(Application::STATUS, '!=', ApplicationStatusEnum::CANCELLED->value)
            ->whereNull(Application::DELETED_AT)
            ->first();
    }

    public function findCancelledByJobRequestAndUser(int $jobRequestId, int $userId): ?Application
    {
        return Application::where(Application::JOB_REQUEST_ID, $jobRequestId)
            ->where(Application::USER_ID, $userId)
            ->where(Application::STATUS, ApplicationStatusEnum::CANCELLED->value)
            ->whereNull(Application::DELETED_AT)
            ->first();
    }

    public function reapply(Application $application, array $data): Application
    {
        $application->update($data);
        return $application;
    }

    public function cancel(Application $application): Application
    {
        $application->update([Application::STATUS => ApplicationStatusEnum::CANCELLED->value]);
        return $application;
    }

    public function getByJobRequestId(int $jobRequestId): Collection
    {
        return Application::where(Application::JOB_REQUEST_ID, $jobRequestId)
            ->with(['user.profile'])
            ->orderByDesc(Application::CREATED_AT)
            ->get();
    }

    public function accept(Application $application): Application
    {
        $application->update([Application::STATUS => ApplicationStatusEnum::ACCEPTED->value]);
        return $application;
    }

    public function reject(Application $application): Application
    {
        $application->update([Application::STATUS => ApplicationStatusEnum::REJECTED->value]);
        return $application;
    }

    public function setCompleted(Application $application): Application
    {
        $application->update([Application::STATUS => ApplicationStatusEnum::COMPLETED->value]);
        return $application;
    }

    public function shortlist(Application $application): Application
    {
        $application->update([Application::STATUS => ApplicationStatusEnum::SHORTLISTED->value]);
        return $application;
    }

    public function rejectAllPendingForJob(int $jobRequestId, int $exceptApplicationId): void
    {
        Application::where(Application::JOB_REQUEST_ID, $jobRequestId)
            ->whereIn(Application::STATUS, [
                ApplicationStatusEnum::PENDING->value,
                ApplicationStatusEnum::SHORTLISTED->value,
            ])
            ->where(Application::ID, '!=', $exceptApplicationId)
            ->update([Application::STATUS => ApplicationStatusEnum::REJECTED->value]);
    }

    public function findAcceptedForJob(int $jobRequestId): ?Application
    {
        return Application::where(Application::JOB_REQUEST_ID, $jobRequestId)
            ->where(Application::STATUS, ApplicationStatusEnum::ACCEPTED->value)
            ->first();
    }

    public function getAllBySeekerId(int $seekerId): Collection
    {
        return Application::whereHas(
            'jobRequest',
            fn ($q) => $q->where('user_id', $seekerId)
        )
            ->with(['jobRequest', 'user.profile'])
            ->orderByDesc(Application::CREATED_AT)
            ->get();
    }
}
