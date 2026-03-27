<?php

declare(strict_types=1);

namespace App\Services\Repositories\ServiceApplication;

use App\Constants\ErrorMessages;
use App\DataTransferObjects\ServiceApplication\SaveServiceApplicationData;
use App\Enums\Job\ApplicationStatusEnum;
use App\Models\Service;
use App\Models\ServiceApplication;
use Illuminate\Support\Collection;

class ServiceApplicationLogicRepository
{
    public function __construct(
        private readonly ServiceApplicationDbRepository $dbRepository
    ) {
    }

    public function createApplication(SaveServiceApplicationData $dto): ServiceApplication
    {
        $service = Service::findOrFail($dto->serviceId);

        if ($service->getUserId() === $dto->userId) {
            abort(403, ErrorMessages::OWN_SERVICE_APPLICATION);
        }

        if ($this->dbRepository->findByServiceAndUser($dto->serviceId, $dto->userId)) {
            abort(422, ErrorMessages::SERVICE_APPLICATION_ALREADY_APPLIED);
        }

        return $this->dbRepository->create($dto->toArray());
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
}
