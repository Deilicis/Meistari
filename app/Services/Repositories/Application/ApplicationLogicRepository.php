<?php

declare(strict_types=1);

namespace App\Services\Repositories\Application;

use App\Constants\ErrorMessages;
use App\DataTransferObjects\Application\SaveApplicationData;
use App\Enums\Job\ApplicationStatusEnum;
use App\Models\Application;
use App\Models\JobRequest;
use Illuminate\Support\Collection;

class ApplicationLogicRepository
{
    public function __construct(
        private readonly ApplicationDbRepository $dbRepository
    ) {}

    public function createApplication(SaveApplicationData $dto): Application
    {
        $jobRequest = JobRequest::findOrFail($dto->jobRequestId);

        if ($jobRequest->getUserId() === $dto->userId) {
            abort(403, ErrorMessages::OWN_JOB_REQUEST_APPLICATION);
        }

        if ($this->dbRepository->findByJobRequestAndUser($dto->jobRequestId, $dto->userId)) {
            abort(422, ErrorMessages::JOB_APPLICATION_ALREADY_APPLIED);
        }

        return $this->dbRepository->create($dto->toArray());
    }

    public function getAppliedJobIds(int $userId): array
    {
        return $this->dbRepository->getAppliedJobIds($userId);
    }

    public function getMasterApplications(int $userId): Collection
    {
        return $this->dbRepository->getByUserIdWithRelations($userId);
    }

    public function cancelApplication(int $id, int $userId): Application
    {
        $application = $this->dbRepository->findByIdForUser($id, $userId);

        if (!$application) {
            abort(403, ErrorMessages::APPLICATION_CANCEL_FORBIDDEN);
        }

        $cancellable = [ApplicationStatusEnum::PENDING, ApplicationStatusEnum::REJECTED];
        if (!in_array($application->getStatus(), $cancellable)) {
            abort(422, ErrorMessages::APPLICATION_NOT_CANCELLABLE);
        }

        return $this->dbRepository->cancel($application);
    }
}
