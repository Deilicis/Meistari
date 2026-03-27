<?php

declare(strict_types=1);

namespace App\Services\Repositories\Application;

use App\Constants\ErrorMessages;
use App\DataTransferObjects\Application\SaveApplicationData;
use App\Enums\Job\ApplicationStatusEnum;
use App\Enums\Job\JobStatusEnum;
use App\Models\Application;
use App\Models\JobRequest;
use App\Services\Repositories\JobRequest\JobRequestDbRepository;
use Illuminate\Support\Collection;

class ApplicationLogicRepository
{
    public function __construct(
        private readonly ApplicationDbRepository    $dbRepository,
        private readonly JobRequestDbRepository     $jobRequestDbRepository,
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

        $cancelled = $this->dbRepository->findCancelledByJobRequestAndUser($dto->jobRequestId, $dto->userId);
        if ($cancelled) {
            return $this->dbRepository->reapply($cancelled, $dto->toArray());
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

    public function getJobApplications(int $jobRequestId, int $seekerId): Collection
    {
        $jobRequest = JobRequest::findOrFail($jobRequestId);

        if ($jobRequest->getUserId() !== $seekerId) {
            abort(403, ErrorMessages::JOB_NOT_YOURS);
        }

        return $this->dbRepository->getByJobRequestId($jobRequestId);
    }

    public function acceptApplication(int $applicationId, int $seekerId): Application
    {
        $application = Application::with('jobRequest')->findOrFail($applicationId);
        $jobRequest  = $application->jobRequest;

        if ($jobRequest->getUserId() !== $seekerId) {
            abort(403, ErrorMessages::JOB_NOT_YOURS);
        }

        if ($jobRequest->getStatus() !== JobStatusEnum::ACTIVE) {
            abort(422, ErrorMessages::JOB_NOT_ACTIVE);
        }

        if ($application->getStatus() !== ApplicationStatusEnum::PENDING) {
            abort(422, ErrorMessages::APPLICATION_NOT_PENDING);
        }

        $accepted = $this->dbRepository->accept($application);
        $this->dbRepository->rejectAllPendingForJob($jobRequest->getId(), $applicationId);
        $this->jobRequestDbRepository->setAssigned($jobRequest);

        return $accepted;
    }

    public function rejectApplication(int $applicationId, int $seekerId): Application
    {
        $application = Application::with('jobRequest')->findOrFail($applicationId);
        $jobRequest  = $application->jobRequest;

        if ($jobRequest->getUserId() !== $seekerId) {
            abort(403, ErrorMessages::JOB_NOT_YOURS);
        }

        if ($application->getStatus() !== ApplicationStatusEnum::PENDING) {
            abort(422, ErrorMessages::APPLICATION_NOT_PENDING);
        }

        return $this->dbRepository->reject($application);
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
