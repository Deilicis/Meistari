<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\JobDispute;
use Illuminate\Support\Carbon;

class JobDisputeRepository
{
    public function create(int $jobRequestId, int $userId, string $reason): JobDispute
    {
        return JobDispute::create([
            JobDispute::JOB_REQUEST_ID    => $jobRequestId,
            JobDispute::RAISED_BY_USER_ID => $userId,
            JobDispute::REASON            => $reason,
        ]);
    }

    public function markResolved(int $id, string $note): void
    {
        JobDispute::where(JobDispute::ID, $id)->update([
            JobDispute::RESOLVED_AT     => Carbon::now(),
            JobDispute::RESOLUTION_NOTE => $note,
        ]);
    }
}
