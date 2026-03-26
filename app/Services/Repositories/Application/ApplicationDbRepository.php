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
            ->whereNull(Application::DELETED_AT)
            ->first();
    }

    public function cancel(Application $application): Application
    {
        $application->update([Application::STATUS => ApplicationStatusEnum::CANCELLED->value]);
        return $application;
    }
}
