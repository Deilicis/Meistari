<?php

declare(strict_types=1);

namespace App\Services\Repositories\ServiceApplication;

use App\DataTransferObjects\ServiceApplication\SaveServiceApplicationData;
use App\Models\Service;
use App\Models\ServiceApplication;

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
            abort(403, 'Nevar pieteikties uz savu pakalpojumu.');
        }

        return $this->dbRepository->create($dto->toArray());
    }

    public function getAppliedServiceIds(int $userId): array
    {
        return $this->dbRepository->getAppliedServiceIds($userId);
    }
}
