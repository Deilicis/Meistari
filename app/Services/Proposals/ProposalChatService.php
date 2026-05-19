<?php

declare(strict_types=1);

namespace App\Services\Proposals;

use App\DataTransferObjects\Chat\SendMessageData;
use App\Enums\MessageTypeEnum;
use App\Enums\PriceProposalStatusEnum;
use App\Events\MessageSent;
use App\Models\Application;
use App\Models\PriceProposal;
use App\Repositories\JobRequestRepository;
use App\Services\Repositories\Chat\ConversationDbRepository;
use App\Services\Repositories\Chat\MessageDbRepository;

class ProposalChatService
{
    public function __construct(
        private readonly ConversationDbRepository $conversationRepo,
        private readonly MessageDbRepository      $messageRepo,
        private readonly JobRequestRepository     $jobRequestRepo,
    ) {}

    public function postEvent(PriceProposal $proposal, string $event, int $actorId): void
    {
        $application = $proposal->application ?? Application::find($proposal->getJobApplicationId());
        $job = $this->jobRequestRepo->findById($application->getJobRequestId());

        $masterId = $application->getUserId();
        $seekerId = $job->getUserId();

        $conversation = $this->conversationRepo->findBetweenUsers($masterId, $seekerId)
            ?? $this->conversationRepo->create($masterId, $seekerId);

        $body = $this->buildFallbackBody($proposal, $event, $actorId, $masterId, $seekerId);

        $message = $this->messageRepo->create(new SendMessageData(
            conversationId: $conversation->getId(),
            senderId:       $actorId,
            body:           $body,
            type:           MessageTypeEnum::PROPOSAL,
            proposalId:     $proposal->getId(),
        ));

        $message->load(['sender', 'proposal.proposedBy', 'proposal.respondedBy']);

        try {
            MessageSent::dispatch($message);
        } catch (\Throwable) {

        }
    }

    private function buildFallbackBody(
        PriceProposal $proposal,
        string        $event,
        int           $actorId,
        int           $masterId,
        int           $seekerId,
    ): string {
        $amount = '€' . number_format($proposal->getAmount(), 2);

        return match ($event) {
            'submitted' => "Jauns cenas piedāvājums: {$amount}",
            'countered' => "Pretpiedāvājums: {$amount}",
            'accepted' => "Pieņēma piedāvājumu {$amount}",
            'rejected' => 'Noraidīja piedāvājumu',
            'withdrawn' => 'Atsauca piedāvājumu',
            default => "Cenas piedāvājums: {$amount}",
        };
    }
}
