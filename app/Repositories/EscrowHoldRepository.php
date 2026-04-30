<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\EscrowStatusEnum;
use App\Models\EscrowHold;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EscrowHoldRepository
{
    public function create(array $data): EscrowHold
    {
        return EscrowHold::create($data);
    }

    public function findByJobRequest(int $jobRequestId): ?EscrowHold
    {
        return EscrowHold::where(EscrowHold::JOB_REQUEST_ID, $jobRequestId)->first();
    }

    public function markReleased(int $id): void
    {
        EscrowHold::where(EscrowHold::ID, $id)->update([
            EscrowHold::STATUS      => EscrowStatusEnum::RELEASED->value,
            EscrowHold::RELEASED_AT => Carbon::now(),
        ]);
    }

    public function markRefunded(int $id): void
    {
        EscrowHold::where(EscrowHold::ID, $id)->update([
            EscrowHold::STATUS      => EscrowStatusEnum::REFUNDED->value,
            EscrowHold::REFUNDED_AT => Carbon::now(),
        ]);
    }

    public function setAutoReleaseAt(int $id, Carbon $at): void
    {
        EscrowHold::where(EscrowHold::ID, $id)->update([EscrowHold::AUTO_RELEASE_AT => $at]);
    }

    public function getDueForAutoRelease(): Collection
    {
        return EscrowHold::where(EscrowHold::STATUS, EscrowStatusEnum::HELD->value)
            ->whereNotNull(EscrowHold::AUTO_RELEASE_AT)
            ->where(EscrowHold::AUTO_RELEASE_AT, '<=', Carbon::now())
            ->get();
    }
}
