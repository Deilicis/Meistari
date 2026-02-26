<?php

declare(strict_types=1);

namespace App\Services\Repositories\JobRequest;

use App\Models\JobRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class JobRequestDbRepository
{
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return JobRequest::with(['category', 'user'])
            ->latest()
            ->paginate($perPage);
    }

    public function getByUserId(int $userId): Collection
    {
        return JobRequest::with(['category', 'applications'])
            ->where(JobRequest::USER_ID, $userId)
            ->latest()
            ->get();
    }

    public function getById(int $id): JobRequest
    {
        return JobRequest::with(['category', 'user', 'applications'])->findOrFail($id);
    }

    public function create(array $data): JobRequest
    {
        return JobRequest::create($data);
    }

    public function update(JobRequest $jobRequest, array $data): bool
    {
        return $jobRequest->update($data);
    }

    public function delete(JobRequest $jobRequest): ?bool
    {
        return $jobRequest->delete();
    }
}