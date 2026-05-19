<?php

declare(strict_types=1);

namespace App\Services\Jobs;

use App\Enums\EscrowStatusEnum;
use App\Models\EscrowHold;
use App\Repositories\EscrowHoldRepository;
use Illuminate\Support\Carbon;

class EscrowService
{
    public function __construct(
        private readonly EscrowHoldRepository $repository,
    ) {}

    public function createHold(
        int $jobRequestId,
        int $clientId,
        int $masterId,
        float $amount,
    ): EscrowHold {
        return $this->repository->create([
            EscrowHold::JOB_REQUEST_ID => $jobRequestId,
            EscrowHold::CLIENT_ID => $clientId,
            EscrowHold::MASTER_ID => $masterId,
            EscrowHold::AMOUNT => $amount,
            EscrowHold::STATUS => EscrowStatusEnum::HELD->value,
            EscrowHold::HELD_AT => Carbon::now(),
        ]);
    }

    public function release(int $jobRequestId): void
    {
        $hold = $this->repository->findByJobRequest($jobRequestId);
        if ($hold) {
            $this->repository->markReleased($hold->getId());
        }
    }

    public function refund(int $jobRequestId): void
    {
        $hold = $this->repository->findByJobRequest($jobRequestId);
        if ($hold) {
            $this->repository->markRefunded($hold->getId());
        }
    }
}
