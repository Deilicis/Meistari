<?php

declare(strict_types=1);

namespace App\Services\Repositories\Service;

use App\DataTransferObjects\Service\SaveServiceRequestData;
use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ServiceLogicRepository
{
    public function __construct(
        private readonly ServiceDbRepository $dbRepository
    ) {
    }

    public function getPaginatedServices(): LengthAwarePaginator
    {
        return $this->dbRepository->getPaginated();
    }

    public function getUserServices(int $userId, array $filters = [])
    {
        return $this->dbRepository->getUserServices($userId, $filters);
    }

    public function getServiceById(int $id): Service
    {
        return $this->dbRepository->getById($id);
    }

    public function createService(SaveServiceRequestData $dto): Service
    {
        return $this->dbRepository->create($dto->toArray());
    }

    public function updateService(Service $service, SaveServiceRequestData $dto, int $currentUserId): bool
    {
        if ($service->user_id !== $currentUserId) {
            abort(403, 'Jums nav tiesību rediģēt šo pakalpojumu.');
        }

        return $this->dbRepository->update($service, $dto->toArray());
    }

    public function deleteService(Service $service, int $currentUserId): ?bool
    {
        if ($service->user_id !== $currentUserId) {
            abort(403, 'Jums nav tiesību dzēst šo pakalpojumu.');
        }

        return $this->dbRepository->delete($service);
    }
}