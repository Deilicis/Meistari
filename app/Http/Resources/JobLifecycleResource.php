<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\Job\JobStatusEnum;
use App\Models\EscrowHold;
use App\Models\JobRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin JobRequest
 */
class JobLifecycleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user   = $request->user();
        $userId = $user?->getId();
        $status = $this->getStatus();

        $isClient = $this->getUserId() === $userId;
        $isMaster = $this->getMasterId() !== null && $this->getMasterId() === $userId;

        return [
            JobRequest::ID              => $this->getId(),
            JobRequest::TITLE           => $this->getTitle(),
            JobRequest::DESCRIPTION     => $this->getDescription(),
            JobRequest::STATUS          => $status->value,
            'status_label'              => $status->label(),
            JobRequest::AGREED_PRICE    => $this->getAgreedPrice(),
            JobRequest::PRICE_TYPE      => $this->getPriceType(),
            JobRequest::MASTER_COMPLETED_AT => $this->getMasterCompletedAt()?->toISOString(),
            JobRequest::COMPLETED_AT    => $this->getCompletedAt()?->toISOString(),
            JobRequest::CREATED_AT      => $this->getCreatedAt()?->toISOString(),
            'client' => [
                'id'   => $this->user->getId(),
                'name' => $this->user->getName(),
            ],
            'master' => $this->master ? [
                'id'   => $this->master->getId(),
                'name' => $this->master->getName(),
            ] : null,
            'escrow' => $this->escrowHold ? [
                'status'          => $this->escrowHold->getStatus()->value,
                'amount'          => $this->escrowHold->getAmount(),
                'held_at'         => $this->escrowHold->getHeldAt()?->toISOString(),
                'auto_release_at' => $this->escrowHold->getAutoReleaseAt()?->toISOString(),
            ] : null,
            'allowed_actions' => $this->computeAllowedActions($status, $isClient, $isMaster),
        ];
    }

    private function computeAllowedActions(JobStatusEnum $status, bool $isClient, bool $isMaster): array
    {
        return match ($status) {
            JobStatusEnum::OPEN                  => $isClient ? ['accept_application'] : [],
            JobStatusEnum::ACCEPTED              => $isClient ? ['pay', 'cancel'] : ($isMaster ? ['cancel'] : []),
            JobStatusEnum::IN_PROGRESS           => $isMaster ? ['mark_complete'] : [],
            JobStatusEnum::AWAITING_CONFIRMATION => $isClient ? ['confirm', 'dispute'] : ($isMaster ? ['dispute'] : []),
            JobStatusEnum::DISPUTED              => ($isClient || $isMaster) ? ['cancel'] : [],
            default                              => [],
        };
    }
}
