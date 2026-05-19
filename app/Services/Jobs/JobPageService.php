<?php

declare(strict_types=1);

namespace App\Services\Jobs;

use App\Enums\Job\ApplicationStatusEnum;
use App\Enums\Job\JobStatusEnum;
use App\Enums\Role\RoleNameEnum;
use App\Models\Application;
use App\Models\JobRequest;
use App\Models\Role;
use App\Models\User;
use App\Enums\PriceProposalStatusEnum;
use App\Http\Resources\PriceProposalResource;
use App\Models\PriceProposal;
use App\Repositories\JobRequestRepository;
use App\Repositories\PriceProposalRepository;
use App\Services\Repositories\Application\ApplicationDbRepository;
use App\Services\Repositories\Chat\ConversationDbRepository;

class JobPageService
{
    public function __construct(
        private readonly JobRequestRepository     $jobRequestRepo,
        private readonly ApplicationDbRepository  $applicationRepo,
        private readonly ConversationDbRepository $conversationRepo,
        private readonly PriceProposalRepository  $proposalRepo,
    ) {}

    public function buildPayloadForUser(int $jobId, User $user): array
    {
        $job = JobRequest::with(['user', 'master', 'escrowHold', 'acceptedApplication', 'pendingCategorySuggestion'])
            ->findOrFail($jobId);

        $viewerRole = $this->determineViewerRole($job, $user);

        if ($viewerRole === null) {
            abort(403, 'Šim darbam nav piekļuves.');
        }

        return [
            'job' => $this->serializeJob($job),
            'viewer_role' => $viewerRole,
            'applications' => $this->getApplications($job, $user, $viewerRole),
            'own_application' => $this->getOwnApplication($job, $user, $viewerRole),
            'allowed_actions' => $this->computeAllowedActions($job, $user, $viewerRole),
            'chat' => $this->getChatInfo($job, $user, $viewerRole),
            'proposals' => $this->getProposals($job, $user, $viewerRole),
        ];
    }

    private function determineViewerRole(JobRequest $job, User $user): ?string
    {
        $isAdmin = $user->roles->contains(
            fn (Role $role) => $role->getName() === RoleNameEnum::ADMIN || $role->getName() === RoleNameEnum::MODERATOR
        );
        if ($isAdmin) {
            return 'admin';
        }

        if ($job->getUserId() === $user->getId()) {
            return 'owner';
        }

        if ($job->getMasterId() !== null && $job->getMasterId() === $user->getId()) {
            return 'accepted_master';
        }

        $ownApp = $this->applicationRepo->findByJobRequestAndUser($job->getId(), $user->getId());
        if ($ownApp) {
            return 'applicant';
        }

        return null;
    }

    private function getApplications(JobRequest $job, User $user, string $viewerRole): ?array
    {
        if ($viewerRole !== 'owner' && $viewerRole !== 'admin') {
            return null;
        }

        $apps = $this->applicationRepo->getByJobRequestId($job->getId());

        return $apps->map(fn (Application $a) => $this->serializeApplication($a))->values()->toArray();
    }

    private function getOwnApplication(JobRequest $job, User $user, string $viewerRole): ?array
    {
        if ($viewerRole !== 'applicant') {
            return null;
        }

        $app = $this->applicationRepo->findByJobRequestAndUser($job->getId(), $user->getId());

        return $app ? $this->serializeApplication($app) : null;
    }

    private function computeAllowedActions(JobRequest $job, User $user, string $viewerRole): array
    {
        $actions = [];
        $status = $job->getStatus();

        switch ($viewerRole) {
            case 'owner':
                if ($status === JobStatusEnum::OPEN) {
                    $actions = array_merge($actions, ['shortlist_application', 'accept_application', 'reject_application', 'edit_job', 'delete_job', 'cancel']);
                }
                if ($status === JobStatusEnum::ACCEPTED) {
                    $actions = array_merge($actions, ['pay', 'cancel']);
                }
                if ($status === JobStatusEnum::AWAITING_CONFIRMATION) {
                    $actions = array_merge($actions, ['confirm_complete', 'dispute']);
                }
                if ($status === JobStatusEnum::DISPUTED) {
                    $actions[] = 'cancel';
                }
                if (in_array($status, [JobStatusEnum::ACCEPTED, JobStatusEnum::IN_PROGRESS, JobStatusEnum::AWAITING_CONFIRMATION, JobStatusEnum::DISPUTED])) {
                    $actions[] = 'chat_with_master';
                }
                // Also allow chat if a shortlisted application exists
                if ($status === JobStatusEnum::OPEN) {
                    $hasShortlisted = Application::where(Application::JOB_REQUEST_ID, $job->getId())
                        ->where(Application::STATUS, ApplicationStatusEnum::SHORTLISTED->value)
                        ->exists();
                    if ($hasShortlisted) {
                        $actions[] = 'chat_with_master';
                    }
                }
                break;

            case 'accepted_master':
                if ($status === JobStatusEnum::IN_PROGRESS) {
                    $actions[] = 'mark_complete';
                }
                if ($status === JobStatusEnum::AWAITING_CONFIRMATION) {
                    $actions[] = 'dispute';
                }
                $actions[] = 'chat_with_seeker';
                break;

            case 'applicant':
                $ownApp = $this->applicationRepo->findByJobRequestAndUser($job->getId(), $user->getId());
                if ($ownApp && in_array($ownApp->getStatus(), [ApplicationStatusEnum::PENDING, ApplicationStatusEnum::SHORTLISTED])) {
                    $actions[] = 'withdraw_application';
                }
                if ($ownApp && in_array($ownApp->getStatus(), [ApplicationStatusEnum::SHORTLISTED, ApplicationStatusEnum::ACCEPTED])) {
                    $actions[] = 'chat_with_seeker';
                }
                break;

            case 'admin':
                if ($status === JobStatusEnum::DISPUTED) {
                    $actions = array_merge($actions, ['resolve_dispute_release', 'resolve_dispute_refund']);
                }
                break;
        }

        return array_values(array_unique($actions));
    }

    private function getChatInfo(JobRequest $job, User $user, string $viewerRole): ?array
    {
        $otherUserId = match ($viewerRole) {
            'owner' => $job->getMasterId()
                ?? Application::where(Application::JOB_REQUEST_ID, $job->getId())
                    ->whereIn(Application::STATUS, [
                        ApplicationStatusEnum::SHORTLISTED->value,
                        ApplicationStatusEnum::PENDING->value,
                    ])
                    ->orderByRaw('FIELD(status, ?, ?)', [
                        ApplicationStatusEnum::SHORTLISTED->value,
                        ApplicationStatusEnum::PENDING->value,
                    ])
                    ->value(Application::USER_ID),
            'accepted_master', 'applicant' => $job->getUserId(),
            default => null,
        };

        if ($otherUserId === null) {
            return null;
        }

        $conversation = $this->conversationRepo->findBetweenUsers($user->getId(), $otherUserId);

        return [
            'other_user_id' => $otherUserId,
            'conversation_id' => $conversation?->getId(),
        ];
    }

    private function getProposals(JobRequest $job, User $user, string $viewerRole): ?array
    {
        $ownApp = match ($viewerRole) {
            'applicant' => $this->applicationRepo->findByJobRequestAndUser($job->getId(), $user->getId()),
            'accepted_master' => $this->applicationRepo->findAcceptedForJob($job->getId()),
            default => null,
        };

        if ($ownApp === null) {
            return null;
        }

        $pending = $this->proposalRepo->findPendingForApplication($ownApp->getId());
        $history = $this->proposalRepo->getHistoryForApplication($ownApp->getId());

        $hasNegotiableStatus = $job->getStatus() === JobStatusEnum::OPEN
            && in_array($ownApp->getStatus(), [
                ApplicationStatusEnum::PENDING,
                ApplicationStatusEnum::SHORTLISTED,
            ], true);

        $canAct = $pending !== null
            && $pending->getProposedByUserId() !== $user->getId();

        return [
            'application_id' => $ownApp->getId(),
            'pending' => $pending ? PriceProposalResource::make($pending->loadMissing(['proposedBy', 'respondedBy']))->resolve() : null,
            'history' => PriceProposalResource::collection($history)->resolve(),
            'can_submit_fresh'=> $hasNegotiableStatus && $pending === null,
            'can_counter' => $canAct,
            'can_accept' => $canAct,
            'can_reject' => $canAct,
            'can_withdraw' => $pending !== null && $pending->getProposedByUserId() === $user->getId(),
        ];
    }

    private function serializeJob(JobRequest $job): array
    {
        $status = $job->getStatus();

        return [
            'id' => $job->getId(),
            'title' => $job->getTitle(),
            'description' => $job->getDescription(),
            'status' => $status->value,
            'status_label' => $status->label(),
            'budget' => $job->getBudget(),
            'agreed_price' => $job->getAgreedPrice(),
            'price_type' => $job->getPriceType(),
            'deadline' => $job->getDeadline(),
            'location' => $job->getLocation(),
            'images' => $job->getImages(),
            'created_at' => $job->getCreatedAt()?->toISOString(),
            'master_completed_at' => $job->getMasterCompletedAt()?->toISOString(),
            'completed_at' => $job->getCompletedAt()?->toISOString(),
            'client' => [
                'id' => $job->user->getId(),
                'name' => $job->user->getName(),
            ],
            'master' => $job->master ? [
                'id' => $job->master->getId(),
                'name' => $job->master->getName(),
            ] : null,
            'escrow' => $job->escrowHold ? [
                'status' => $job->escrowHold->getStatus()->value,
                'amount' => $job->escrowHold->getAmount(),
                'held_at' => $job->escrowHold->getHeldAt()?->toISOString(),
                'auto_release_at' => $job->escrowHold->getAutoReleaseAt()?->toISOString(),
            ] : null,
            'pending_category_suggestion' => $job->pendingCategorySuggestion ? [
                'id' => $job->pendingCategorySuggestion->getId(),
                'name' => $job->pendingCategorySuggestion->getName(),
                'status' => $job->pendingCategorySuggestion->getStatus()->value,
                'review_note' => $job->pendingCategorySuggestion->getReviewNote(),
            ] : null,
        ];
    }

    private function serializeApplication(Application $app): array
    {
        $profile = $app->user?->profile;
        $pendingProposal = $this->proposalRepo->findPendingForApplication($app->getId());

        return [
            'id' => $app->getId(),
            'cover_letter' => $app->getCoverLetter(),
            'price_offer' => $app->getPriceOffer(),
            'status' => $app->getStatus()->value,
            'created_at' => $app->getCreatedAt()?->toISOString(),
            'pending_proposal'=> $pendingProposal
                ? PriceProposalResource::make($pendingProposal->loadMissing(['proposedBy', 'respondedBy']))->resolve()
                : null,
            'user' => $app->user ? [
                'id' => $app->user->getId(),
                'name' => $app->user->getName(),
                'profile' => $profile ? [
                    'first_name' => $profile->getFirstName(),
                    'last_name' => $profile->getLastName(),
                    'company_name' => $profile->getCompanyName(),
                    'type' => $profile->getType()?->value,
                    'city' => $profile->getCity(),
                    'avatar' => $profile->getAvatar(),
                ] : null,
            ] : null,
        ];
    }
}
