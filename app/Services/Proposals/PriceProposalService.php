<?php

declare(strict_types=1);

namespace App\Services\Proposals;

use App\DTOs\Notifications\CreateNotificationDTO;
use App\DTOs\Proposals\CounterProposalDTO;
use App\DTOs\Proposals\RespondToProposalDTO;
use App\DTOs\Proposals\SubmitProposalDTO;
use App\Enums\Job\ApplicationStatusEnum;
use App\Enums\Job\JobStatusEnum;
use App\Enums\NotificationTypeEnum;
use App\Enums\PriceProposalStatusEnum;
use App\Exceptions\Proposals\ApplicationNotNegotiableException;
use App\Exceptions\Proposals\InvalidProposalActionException;
use App\Exceptions\Proposals\ProposalAuthorizationException;
use App\Models\Application;
use App\Models\PriceProposal;
use App\Repositories\JobRequestRepository;
use App\Repositories\PriceProposalRepository;
use App\Services\NotificationService;
use App\Services\Repositories\Application\ApplicationDbRepository;
use Illuminate\Support\Facades\DB;

class PriceProposalService
{
    private const NEGOTIABLE_STATUSES = [
        ApplicationStatusEnum::PENDING,
        ApplicationStatusEnum::SHORTLISTED,
    ];

    public function __construct(
        private readonly PriceProposalRepository $proposalRepo,
        private readonly ApplicationDbRepository $applicationRepo,
        private readonly JobRequestRepository    $jobRequestRepo,
        private readonly NotificationService     $notificationService,
        private readonly ProposalChatService     $chatService,
    ) {}

    public function submitInitialProposal(int $applicationId, int $masterId, float $amount, ?string $note): PriceProposal
    {
        return $this->proposalRepo->create(new SubmitProposalDTO(
            jobApplicationId: $applicationId,
            proposedByUserId: $masterId,
            amount:           $amount,
            note:             $note,
        ));
    }

    public function accept(RespondToProposalDTO $dto): PriceProposal
    {
        return DB::transaction(function () use ($dto): PriceProposal {
            $proposal    = $this->loadProposal($dto->proposalId);
            $application = $proposal->application;

            $this->assertNegotiable($application);
            $this->assertPending($proposal);
            $this->assertOppositeParty($application, $dto->respondedByUserId, $proposal->getProposedByUserId());

            $this->proposalRepo->markStatus($proposal->getId(), PriceProposalStatusEnum::ACCEPTED, $dto->respondedByUserId);

            $application->update([Application::PRICE_OFFER => $proposal->getAmount()]);

            $job = $this->jobRequestRepo->findById($application->getJobRequestId());

            $this->jobRequestRepo->setAcceptedApplication(
                jobId:          $job->getId(),
                applicationId:  $application->getId(),
                masterId:       $application->getUserId(),
                agreedPrice:    $proposal->getAmount(),
                priceType:      'fixed',
            );

            $this->applicationRepo->accept($application);
            $this->applicationRepo->rejectAllPendingForJob($job->getId(), $application->getId());

            $this->notificationService->create(new CreateNotificationDTO(
                userId:    $proposal->getProposedByUserId(),
                type:      NotificationTypeEnum::PROPOSAL_ACCEPTED,
                title:     'Tavs piedāvājums tika pieņemts!',
                body:      $this->otherPartyName($application, $dto->respondedByUserId) . ' pieņēma tavu piedāvājumu €' . number_format($proposal->getAmount(), 2),
                actionUrl: route('jobs.show', $job->getId()),
                metadata:  ['job_request_id' => $job->getId(), 'application_id' => $application->getId()],
            ));

            $fresh = $proposal->fresh(['application.jobRequest']);
            $this->chatService->postEvent($fresh, 'accepted', $dto->respondedByUserId);

            return $fresh;
        });
    }

    public function counter(CounterProposalDTO $dto): PriceProposal
    {
        return DB::transaction(function () use ($dto): PriceProposal {
            $current     = $this->loadProposal($dto->currentProposalId);
            $application = $current->application;

            $this->assertNegotiable($application);
            $this->assertPending($current);
            $this->assertOppositeParty($application, $dto->counteredByUserId, $current->getProposedByUserId());

            $this->proposalRepo->markStatus($current->getId(), PriceProposalStatusEnum::COUNTERED, $dto->counteredByUserId);

            $newProposal = $this->proposalRepo->create(new SubmitProposalDTO(
                jobApplicationId: $application->getId(),
                proposedByUserId: $dto->counteredByUserId,
                amount:           $dto->newAmount,
                note:             $dto->newNote,
            ));

            $job = $this->jobRequestRepo->findById($application->getJobRequestId());

            $this->notificationService->create(new CreateNotificationDTO(
                userId:    $current->getProposedByUserId(),
                type:      NotificationTypeEnum::PROPOSAL_RECEIVED,
                title:     'Saņemts pretpiedāvājums',
                body:      $this->otherPartyName($application, $dto->counteredByUserId) . ' pretpiedāvāja €' . number_format($dto->newAmount, 2),
                actionUrl: route('jobs.show', $job->getId()),
                metadata:  ['job_request_id' => $job->getId(), 'application_id' => $application->getId()],
            ));

            $newProposal->load('application.jobRequest');
            $this->chatService->postEvent($newProposal, 'countered', $dto->counteredByUserId);

            return $newProposal;
        });
    }

    public function reject(RespondToProposalDTO $dto): PriceProposal
    {
        $proposal    = $this->loadProposal($dto->proposalId);
        $application = $proposal->application;

        $this->assertNegotiable($application);
        $this->assertPending($proposal);
        $this->assertOppositeParty($application, $dto->respondedByUserId, $proposal->getProposedByUserId());

        $this->proposalRepo->markStatus($proposal->getId(), PriceProposalStatusEnum::REJECTED, $dto->respondedByUserId);

        $job = $this->jobRequestRepo->findById($application->getJobRequestId());

        $this->notificationService->create(new CreateNotificationDTO(
            userId:    $proposal->getProposedByUserId(),
            type:      NotificationTypeEnum::PROPOSAL_REJECTED,
            title:     'Tavs piedāvājums tika noraidīts',
            body:      $this->otherPartyName($application, $dto->respondedByUserId) . ' noraidīja tavu piedāvājumu',
            actionUrl: route('jobs.show', $job->getId()),
            metadata:  ['job_request_id' => $job->getId(), 'application_id' => $application->getId()],
        ));

        $fresh = $proposal->fresh(['application.jobRequest']);
        $this->chatService->postEvent($fresh, 'rejected', $dto->respondedByUserId);

        return $fresh;
    }

    public function withdraw(RespondToProposalDTO $dto): PriceProposal
    {
        $proposal    = $this->loadProposal($dto->proposalId);
        $application = $proposal->application;

        $this->assertNegotiable($application);
        $this->assertPending($proposal);

        if ($proposal->getProposedByUserId() !== $dto->respondedByUserId) {
            throw new ProposalAuthorizationException('Var atsaukt tikai savus piedāvājumus.');
        }

        $this->proposalRepo->markStatus($proposal->getId(), PriceProposalStatusEnum::WITHDRAWN, $dto->respondedByUserId);

        $job           = $this->jobRequestRepo->findById($application->getJobRequestId());
        $otherPartyId  = $this->getOtherPartyId($application, $dto->respondedByUserId);

        if ($otherPartyId !== null) {
            $this->notificationService->create(new CreateNotificationDTO(
                userId:    $otherPartyId,
                type:      NotificationTypeEnum::PROPOSAL_WITHDRAWN,
                title:     'Piedāvājums tika atsaukts',
                body:      $this->otherPartyName($application, $otherPartyId) . ' atsauca savu piedāvājumu',
                actionUrl: route('jobs.show', $job->getId()),
                metadata:  ['job_request_id' => $job->getId(), 'application_id' => $application->getId()],
            ));
        }

        $fresh = $proposal->fresh(['application.jobRequest']);
        $this->chatService->postEvent($fresh, 'withdrawn', $dto->respondedByUserId);

        return $fresh;
    }

    public function submitFreshProposal(SubmitProposalDTO $dto): PriceProposal
    {
        $application = Application::findOrFail($dto->jobApplicationId);

        $parties = $this->getParties($application);

        if ($dto->proposedByUserId !== $parties['master_id'] && $dto->proposedByUserId !== $parties['seeker_id']) {
            throw new ProposalAuthorizationException('Tu neesi šī pieteikuma dalībnieks.');
        }

        $this->assertNegotiable($application);

        $existing = $this->proposalRepo->findPendingForApplication($application->getId());
        if ($existing !== null) {
            throw new InvalidProposalActionException('Atbildiet uz pašreizējo piedāvājumu pirms iesniegt jaunu.');
        }

        $proposal = $this->proposalRepo->create($dto);

        $job         = $this->jobRequestRepo->findById($application->getJobRequestId());
        $otherPartyId = $this->getOtherPartyId($application, $dto->proposedByUserId);

        if ($otherPartyId !== null) {
            $this->notificationService->create(new CreateNotificationDTO(
                userId:    $otherPartyId,
                type:      NotificationTypeEnum::PROPOSAL_RECEIVED,
                title:     'Jauns cenas piedāvājums',
                body:      $this->otherPartyName($application, $otherPartyId) . ' piedāvāja €' . number_format($dto->amount, 2),
                actionUrl: route('jobs.show', $job->getId()),
                metadata:  ['job_request_id' => $job->getId(), 'application_id' => $application->getId()],
            ));
        }

        $proposal->load('application.jobRequest');
        $this->chatService->postEvent($proposal, 'submitted', $dto->proposedByUserId);

        return $proposal;
    }

    // ─── Private helpers ─────────────────────────────────────────────────────────

    private function loadProposal(int $id): PriceProposal
    {
        $proposal = $this->proposalRepo->findById($id);

        if (!$proposal) {
            abort(404, 'Piedāvājums nav atrasts.');
        }

        $proposal->load('application.jobRequest');

        return $proposal;
    }

    private function assertPending(PriceProposal $proposal): void
    {
        if (!$proposal->isPending()) {
            throw new InvalidProposalActionException('Šis piedāvājums vairs nav aktīvs.');
        }
    }

    private function assertNegotiable(Application $application): void
    {
        if (!in_array($application->getStatus(), self::NEGOTIABLE_STATUSES, true)) {
            throw new ApplicationNotNegotiableException();
        }
    }

    private function assertOppositeParty(Application $application, int $userId, int $proposerId): void
    {
        if ($userId === $proposerId) {
            throw new ProposalAuthorizationException('Nevar reaģēt uz savu piedāvājumu.');
        }

        $parties = $this->getParties($application);

        $isParty = $userId === $parties['master_id'] || $userId === $parties['seeker_id'];
        if (!$isParty) {
            throw new ProposalAuthorizationException('Tu neesi šī pieteikuma dalībnieks.');
        }
    }

    private function getParties(Application $application): array
    {
        $jobRequest = $application->jobRequest ?? $this->jobRequestRepo->findById($application->getJobRequestId());

        return [
            'master_id' => $application->getUserId(),
            'seeker_id' => $jobRequest->getUserId(),
        ];
    }

    private function getOtherPartyId(Application $application, int $userId): ?int
    {
        $parties = $this->getParties($application);

        if ($userId === $parties['master_id']) {
            return $parties['seeker_id'];
        }

        if ($userId === $parties['seeker_id']) {
            return $parties['master_id'];
        }

        return null;
    }

    private function otherPartyName(Application $application, int $viewerUserId): string
    {
        $parties = $this->getParties($application);

        if ($viewerUserId === $parties['seeker_id']) {
            $application->load('user');
            return $application->user?->getName() ?? 'Lietotājs';
        }

        $jobRequest = $application->jobRequest ?? $this->jobRequestRepo->findById($application->getJobRequestId());
        $jobRequest->load('user');
        return $jobRequest->user?->getName() ?? 'Lietotājs';
    }
}
