<?php

declare(strict_types=1);

namespace App\Services\Repositories\Application;

use App\Constants\ErrorMessages;
use App\DataTransferObjects\Application\SaveApplicationData;
use App\DTOs\Notifications\CreateNotificationDTO;
use App\Enums\Job\ApplicationStatusEnum;
use App\Enums\Job\JobStatusEnum;
use App\Enums\NotificationTypeEnum;
use App\Models\Application;
use App\Models\JobRequest;
use App\Models\User;
use App\Notifications\Application\ApplicationAcceptedNotification;
use App\Notifications\Application\ApplicationRejectedNotification;
use App\Notifications\Application\NewJobApplicationNotification;
use App\Services\NotificationService;
use App\Services\Repositories\JobRequest\JobRequestDbRepository;
use Illuminate\Support\Collection;

class ApplicationLogicRepository
{
    public function __construct(
        private readonly ApplicationDbRepository    $dbRepository,
        private readonly JobRequestDbRepository     $jobRequestDbRepository,
        private readonly NotificationService        $notificationService,
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
            $application = $this->dbRepository->reapply($cancelled, $dto->toArray());
        } else {
            $application = $this->dbRepository->create($dto->toArray());
        }

        $applicant = User::find($dto->userId);
        $jobRequest->load('user');
        $jobRequest->user->notify(new NewJobApplicationNotification($application, $applicant));

        $this->notificationService->create(new CreateNotificationDTO(
            userId: $jobRequest->getUserId(),
            type: NotificationTypeEnum::NEW_APPLICATION,
            title: 'Jauns pieteikums tavam sludinājumam',
            body: $applicant->getName() . ' pieteicās uz "' . $jobRequest->getTitle() . '"',
            actionUrl: route('jobs.show', $jobRequest->getId()),
            metadata: ['job_request_id' => $jobRequest->getId(), 'application_id' => $application->getId()],
        ));

        return $application;
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

    public function shortlistApplication(int $applicationId, int $seekerId): Application
    {
        $application = Application::with('jobRequest')->findOrFail($applicationId);
        $jobRequest  = $application->jobRequest;

        if ($jobRequest->getUserId() !== $seekerId) {
            abort(403, ErrorMessages::JOB_NOT_YOURS);
        }

        if ($jobRequest->getStatus() !== JobStatusEnum::OPEN) {
            abort(422, ErrorMessages::JOB_NOT_ACTIVE);
        }

        if ($application->getStatus() !== ApplicationStatusEnum::PENDING) {
            abort(422, 'Pieteikums nav gaidīšanas stāvoklī.');
        }

        $shortlisted = $this->dbRepository->shortlist($application);

        $shortlisted->load('user');
        $this->notificationService->create(new CreateNotificationDTO(
            userId: $shortlisted->getUserId(),
            type: NotificationTypeEnum::APPLICATION_SHORTLISTED,
            title: 'Tavs pieteikums iekļauts īsajā sarakstā!',
            body: 'Tavs pieteikums uz "' . $jobRequest->getTitle() . '" ir apsvēršanā. Vari sazināties ar klientu.',
            actionUrl: route('jobs.show', $jobRequest->getId()),
            metadata: ['job_request_id' => $jobRequest->getId(), 'application_id' => $applicationId],
        ));

        return $shortlisted;
    }

    public function acceptApplication(int $applicationId, int $seekerId): Application
    {
        $application = Application::with('jobRequest')->findOrFail($applicationId);
        $jobRequest  = $application->jobRequest;

        if ($jobRequest->getUserId() !== $seekerId) {
            abort(403, ErrorMessages::JOB_NOT_YOURS);
        }

        if ($jobRequest->getStatus() !== JobStatusEnum::OPEN) {
            abort(422, ErrorMessages::JOB_NOT_ACTIVE);
        }

        if ($application->getStatus() !== ApplicationStatusEnum::PENDING) {
            abort(422, ErrorMessages::APPLICATION_NOT_PENDING);
        }

        $accepted = $this->dbRepository->accept($application);
        $this->dbRepository->rejectAllPendingForJob($jobRequest->getId(), $applicationId);

        $jobRequest->update([
            JobRequest::STATUS                  => JobStatusEnum::ACCEPTED->value,
            JobRequest::MASTER_ID               => $application->getUserId(),
            JobRequest::ACCEPTED_APPLICATION_ID => $applicationId,
            JobRequest::AGREED_PRICE            => $application->getPriceOffer() ?? $jobRequest->getBudget() ?? 0,
            JobRequest::PRICE_TYPE              => 'fixed',
        ]);

        $accepted->load('user');
        $accepted->user->notify(new ApplicationAcceptedNotification($accepted));

        $this->notificationService->create(new CreateNotificationDTO(
            userId: $accepted->getUserId(),
            type: NotificationTypeEnum::APPLICATION_ACCEPTED,
            title: 'Tavs pieteikums tika pieņemts!',
            body: 'Tavs pieteikums uz "' . $jobRequest->getTitle() . '" tika pieņemts.',
            actionUrl: route('jobs.show', $jobRequest->getId()),
            metadata: ['job_request_id' => $jobRequest->getId(), 'application_id' => $applicationId],
        ));

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

        $rejected = $this->dbRepository->reject($application);

        $rejected->load('user');
        $rejected->user->notify(new ApplicationRejectedNotification($rejected));

        $this->notificationService->create(new CreateNotificationDTO(
            userId: $rejected->getUserId(),
            type: NotificationTypeEnum::APPLICATION_REJECTED,
            title: 'Tavs pieteikums netika pieņemts',
            body: 'Tavs pieteikums uz "' . $jobRequest->getTitle() . '" netika pieņemts.',
            actionUrl: route('jobs.show', $jobRequest->getId()),
            metadata: ['job_request_id' => $jobRequest->getId(), 'application_id' => $applicationId],
        ));

        return $rejected;
    }

    public function getSeekerReceivedApplications(int $seekerId): Collection
    {
        return $this->dbRepository->getAllBySeekerId($seekerId);
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
