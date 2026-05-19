<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\Job\JobStatusEnum;
use App\Models\JobRequest;
use Illuminate\Support\Carbon;

class JobRequestRepository
{
    public function findById(int $id): ?JobRequest
    {
        return JobRequest::with(['user', 'master', 'escrowHold', 'acceptedApplication'])->find($id);
    }

    public function updateStatus(int $id, JobStatusEnum $status): void
    {
        JobRequest::where(JobRequest::ID, $id)->update([JobRequest::STATUS => $status->value]);
    }

    public function setAcceptedApplication(
        int $jobId,
        int $applicationId,
        int $masterId,
        float $agreedPrice,
        string $priceType,
    ): void {
        JobRequest::where(JobRequest::ID, $jobId)->update([
            JobRequest::ACCEPTED_APPLICATION_ID => $applicationId,
            JobRequest::MASTER_ID => $masterId,
            JobRequest::AGREED_PRICE => $agreedPrice,
            JobRequest::PRICE_TYPE => $priceType,
            JobRequest::STATUS => JobStatusEnum::ACCEPTED->value,
        ]);
    }

    public function setMasterCompletedAt(int $jobId): void
    {
        JobRequest::where(JobRequest::ID, $jobId)->update([
            JobRequest::MASTER_COMPLETED_AT => Carbon::now(),
            JobRequest::STATUS => JobStatusEnum::AWAITING_CONFIRMATION->value,
        ]);
    }

    public function setCompletedAt(int $jobId): void
    {
        JobRequest::where(JobRequest::ID, $jobId)->update([
            JobRequest::COMPLETED_AT => Carbon::now(),
            JobRequest::STATUS => JobStatusEnum::COMPLETED->value,
        ]);
    }
}
