<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTOs\Proposals\SubmitProposalDTO;
use App\Enums\PriceProposalStatusEnum;
use App\Models\PriceProposal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PriceProposalRepository
{
    public function create(SubmitProposalDTO $dto): PriceProposal
    {
        return PriceProposal::create([
            PriceProposal::JOB_APPLICATION_ID  => $dto->jobApplicationId,
            PriceProposal::PROPOSED_BY_USER_ID => $dto->proposedByUserId,
            PriceProposal::AMOUNT              => $dto->amount,
            PriceProposal::PRICE_TYPE          => 'fixed',
            PriceProposal::NOTE                => $dto->note,
            PriceProposal::STATUS              => PriceProposalStatusEnum::PENDING->value,
        ]);
    }

    public function findById(int $id): ?PriceProposal
    {
        return PriceProposal::find($id);
    }

    public function findPendingForApplication(int $applicationId): ?PriceProposal
    {
        return PriceProposal::where(PriceProposal::JOB_APPLICATION_ID, $applicationId)
            ->where(PriceProposal::STATUS, PriceProposalStatusEnum::PENDING->value)
            ->whereNull(PriceProposal::DELETED_AT)
            ->first();
    }

    public function getHistoryForApplication(int $applicationId): Collection
    {
        return PriceProposal::where(PriceProposal::JOB_APPLICATION_ID, $applicationId)
            ->with(['proposedBy', 'respondedBy'])
            ->orderBy(PriceProposal::CREATED_AT)
            ->get();
    }

    public function markStatus(int $id, PriceProposalStatusEnum $status, int $respondedByUserId): void
    {
        PriceProposal::where(PriceProposal::ID, $id)->update([
            PriceProposal::STATUS               => $status->value,
            PriceProposal::RESPONDED_BY_USER_ID => $respondedByUserId,
            PriceProposal::RESPONDED_AT         => Carbon::now(),
        ]);
    }
}
