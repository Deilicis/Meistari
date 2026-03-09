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

    public function getByUserId(int $userId, array $filters = []): Collection
    {
        $query = JobRequest::with(['category', 'applications'])
            ->where(JobRequest::USER_ID, $userId);

        if (!empty($filters['search'])) {
            $query->where(JobRequest::TITLE, 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['category_id'])) {
            $query->where(JobRequest::CATEGORY_ID, $filters['category_id']);
        }

        if (!empty($filters['status'])) {
            $query->where(JobRequest::STATUS, $filters['status']);
        }

        if (!empty($filters['budget_min'])) {
            $query->where(JobRequest::BUDGET, '>=', $filters['budget_min']);
        }

        if (!empty($filters['budget_max'])) {
            $query->where(JobRequest::BUDGET, '<=', $filters['budget_max']);
        }

        return $query->latest()->get();
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