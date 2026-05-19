<?php

declare(strict_types=1);

namespace App\Services\Repositories\ServiceApplication;

use App\Constants\ErrorMessages;
use App\DataTransferObjects\ServiceApplication\SaveServiceApplicationData;
use App\DTOs\Notifications\CreateNotificationDTO;
use App\Enums\Job\ApplicationStatusEnum;
use App\Enums\NotificationTypeEnum;
use App\Models\Service;
use App\Models\ServiceApplication;
use App\Models\User;
use App\Notifications\Service\NewServiceApplicationNotification;
use App\Services\NotificationService;
use Illuminate\Support\Collection;

class ServiceApplicationLogicRepository
{
    public function __construct(
        private readonly ServiceApplicationDbRepository $dbRepository,
        private readonly NotificationService            $notificationService,
    ) {}

    public function createApplication(SaveServiceApplicationData $dto): ServiceApplication
    {
        $service = Service::findOrFail($dto->serviceId);

        if ($service->getUserId() === $dto->userId) {
            abort(403, ErrorMessages::OWN_SERVICE_APPLICATION);
        }

        if ($this->dbRepository->findByServiceAndUser($dto->serviceId, $dto->userId)) {
            abort(422, ErrorMessages::SERVICE_APPLICATION_ALREADY_APPLIED);
        }

        $application = $this->dbRepository->create($dto->toArray());

        $applicant = User::find($dto->userId);
        $service->load('user');
        $service->user->notify(new NewServiceApplicationNotification($application, $applicant));

        $this->notificationService->create(new CreateNotificationDTO(
            userId: $service->getUserId(),
            type: NotificationTypeEnum::NEW_APPLICATION,
            title: 'Jauns pieteikums tavam pakalpojumam',
            body: $applicant->getName() . ' pieteicās uz "' . $service->getTitle() . '"',
            actionUrl: route('master.service-applications.index'),
            metadata: [
                'service_id' => $service->getId(),
                'application_id' => $application->getId(),
                'applicant_id' => $applicant->getId(),
            ],
        ));

        return $application;
    }

    public function getAppliedServiceIds(int $userId): array
    {
        return $this->dbRepository->getAppliedServiceIds($userId);
    }

    public function getUserApplications(int $userId): Collection
    {
        return $this->dbRepository->getByUserIdWithRelations($userId);
    }

    public function cancelApplication(int $id, int $userId): ServiceApplication
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

    public function getMasterApplications(int $masterId): Collection
    {
        return $this->dbRepository->getForMaster($masterId);
    }

    public function getServiceApplications(int $serviceId, int $masterId): Collection
    {
        $service = Service::where(Service::ID, $serviceId)
            ->where(Service::USER_ID, $masterId)
            ->firstOrFail();

        return $this->dbRepository->getForService($service->getId());
    }

    public function acceptApplication(int $applicationId, int $masterId): ServiceApplication
    {
        $application = $this->dbRepository->findByIdForMaster($applicationId, $masterId);

        if (!$application) {
            abort(403, ErrorMessages::SERVICE_APPLICATION_NOT_YOURS);
        }

        if ($application->getStatus() !== ApplicationStatusEnum::PENDING) {
            abort(422, ErrorMessages::APPLICATION_NOT_PENDING);
        }

        $accepted = $this->dbRepository->updateStatus($application, ApplicationStatusEnum::ACCEPTED);

        $this->notificationService->create(new CreateNotificationDTO(
            userId: $accepted->getUserId(),
            type: NotificationTypeEnum::APPLICATION_ACCEPTED,
            title: 'Tavs pieteikums tika pieņemts!',
            body: 'Tavs pieteikums uz "' . $accepted->service->getTitle() . '" tika pieņemts.',
            actionUrl: route('seeker.service-applications.index'),
            metadata: ['service_id' => $accepted->getServiceId(), 'application_id' => $accepted->getId()],
        ));

        return $accepted;
    }

    public function rejectApplication(int $applicationId, int $masterId): ServiceApplication
    {
        $application = $this->dbRepository->findByIdForMaster($applicationId, $masterId);

        if (!$application) {
            abort(403, ErrorMessages::SERVICE_APPLICATION_NOT_YOURS);
        }

        if ($application->getStatus() !== ApplicationStatusEnum::PENDING) {
            abort(422, ErrorMessages::APPLICATION_NOT_PENDING);
        }

        $rejected = $this->dbRepository->updateStatus($application, ApplicationStatusEnum::REJECTED);

        $this->notificationService->create(new CreateNotificationDTO(
            userId: $rejected->getUserId(),
            type: NotificationTypeEnum::APPLICATION_REJECTED,
            title: 'Tavs pieteikums netika pieņemts',
            body: 'Tavs pieteikums uz "' . $rejected->service->getTitle() . '" netika pieņemts.',
            actionUrl: route('seeker.service-applications.index'),
            metadata: ['service_id' => $rejected->getServiceId(), 'application_id' => $rejected->getId()],
        ));

        return $rejected;
    }
}
