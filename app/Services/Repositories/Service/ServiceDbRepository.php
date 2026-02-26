<?php

declare(strict_types=1);

namespace App\Services\Repositories\Service;

use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ServiceDbRepository
{
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Service::with(['category', 'user'])
            ->latest()
            ->paginate($perPage);
    }

    public function getByUserId(int $userId): Collection
    {
        return Service::with('category')
            ->where(Service::USER_ID, $userId)
            ->latest()
            ->get();
    }

    public function getById(int $id): Service
    {
        return Service::with(['category', 'user'])->findOrFail($id);
    }

    public function create(array $data): Service
    {
        return Service::create($data);
    }

    public function update(Service $service, array $data): bool
    {
        return $service->update($data);
    }

    public function delete(Service $service): ?bool
    {
        return $service->delete();
    }
}