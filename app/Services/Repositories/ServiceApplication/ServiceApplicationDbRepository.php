<?php

declare(strict_types=1);

namespace App\Services\Repositories\ServiceApplication;

use App\Models\ServiceApplication;

class ServiceApplicationDbRepository
{
    public function create(array $data): ServiceApplication
    {
        return ServiceApplication::create($data);
    }

    public function getAppliedServiceIds(int $userId): array
    {
        return ServiceApplication::where(ServiceApplication::USER_ID, $userId)
            ->pluck(ServiceApplication::SERVICE_ID)
            ->toArray();
    }
}
