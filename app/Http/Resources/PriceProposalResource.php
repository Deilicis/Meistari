<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\PriceProposal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PriceProposal
 */
class PriceProposalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->getId(),
            'amount'       => number_format($this->getAmount(), 2, '.', ''),
            'price_type'   => $this->getPriceType(),
            'note'         => $this->getNote(),
            'status'       => $this->getStatus()->value,
            'status_label' => $this->getStatus()->label(),
            'proposed_by'  => [
                'id'   => $this->proposedBy?->getId(),
                'name' => $this->proposedBy?->getName(),
            ],
            'responded_by' => $this->respondedBy ? [
                'id'   => $this->respondedBy->getId(),
                'name' => $this->respondedBy->getName(),
            ] : null,
            'responded_at' => $this->getRespondedAt()?->toISOString(),
            'created_at'   => $this->getCreatedAt()?->toISOString(),
            'is_pending'   => $this->isPending(),
        ];
    }
}
