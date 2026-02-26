<?php

declare(strict_types=1);

namespace App\Services\Repositories\JobRequest;

use App\DataTransferObjects\JobRequest\SaveJobRequestData;
use App\Models\JobRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class JobRequestLogicRepository
{
    public function __construct(
        private readonly JobRequestDbRepository $dbRepository
    ) {
    }

    public function getPaginatedJobRequests(): LengthAwarePaginator
    {
        return $this->dbRepository->getPaginated();
    }

    public function getUserJobRequests(int $userId): Collection
    {
        return $this->dbRepository->getByUserId($userId);
    }

    public function getJobRequestById(int $id): JobRequest
    {
        return $this->dbRepository->getById($id);
    }

    public function createJobRequest(SaveJobRequestData $dto): JobRequest
    {
        return $this->dbRepository->create($dto->toArray());
    }

    public function updateJobRequest(JobRequest $jobRequest, SaveJobRequestData $dto, int $currentUserId): bool
    {
        if ($jobRequest->user_id !== $currentUserId) {
            abort(403, 'Jums nav tiesību rediģēt šo darba sludinājumu.');
        }

        return $this->dbRepository->update($jobRequest, $dto->toArray());
    }

    public function deleteJobRequest(JobRequest $jobRequest, int $currentUserId): ?bool
    {
        if ($jobRequest->user_id !== $currentUserId) {
            abort(403, 'Jums nav tiesību dzēst šo darba sludinājumu.');
        }

        return $this->dbRepository->delete($jobRequest);
    }
}