<?php

declare(strict_types=1);

namespace App\Services\Jobs;

use App\DTOs\Jobs\AcceptApplicationDTO;
use App\DTOs\Jobs\CancelJobDTO;
use App\DTOs\Jobs\ConfirmJobDTO;
use App\DTOs\Jobs\DisputeJobDTO;
use App\DTOs\Jobs\MarkJobCompleteDTO;
use App\DTOs\Jobs\PayJobDTO;
use App\DTOs\Notifications\CreateNotificationDTO;
use App\DTOs\Stripe\CreateCheckoutSessionDTO;
use App\Enums\EscrowStatusEnum;
use App\Enums\Job\JobStatusEnum;
use App\Enums\NotificationTypeEnum;
use App\Models\Application;
use App\Models\EscrowHold;
use App\Models\JobRequest;
use App\Repositories\EscrowHoldRepository;
use App\Repositories\JobDisputeRepository;
use App\Repositories\JobRequestRepository;
use App\Services\NotificationService;
use App\Services\Repositories\Application\ApplicationDbRepository;
use App\Services\Stripe\StripeCheckoutService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class JobLifecycleService
{
    public function __construct(
        private readonly JobStateMachine         $stateMachine,
        private readonly JobRequestRepository    $jobRequestRepo,
        private readonly ApplicationDbRepository $applicationRepo,
        private readonly EscrowHoldRepository    $escrowRepo,
        private readonly JobDisputeRepository    $disputeRepo,
        private readonly EscrowService           $escrowService,
        private readonly NotificationService     $notificationService,
        private readonly StripeCheckoutService   $stripeCheckout,
    ) {}

    public function acceptApplication(AcceptApplicationDTO $dto): JobRequest
    {
        return DB::transaction(function () use ($dto): JobRequest {
            $job = JobRequest::lockForUpdate()->findOrFail($dto->jobRequestId);

            if ($job->getUserId() !== $dto->clientId) {
                abort(403, 'Šis darbs nav tavs.');
            }

            $application = Application::where(Application::ID, $dto->applicationId)
                ->where(Application::JOB_REQUEST_ID, $dto->jobRequestId)
                ->firstOrFail();

            $this->stateMachine->assertCanTransition($job->getStatus(), JobStatusEnum::ACCEPTED);

            $this->jobRequestRepo->setAcceptedApplication(
                $job->getId(),
                $application->getId(),
                $application->getUserId(),
                (float) ($application->getPriceOffer() ?? $job->getBudget() ?? 0),
                'fixed',
            );

            $this->applicationRepo->accept($application);
            $this->applicationRepo->rejectAllPendingForJob($job->getId(), $application->getId());

            $this->notificationService->create(new CreateNotificationDTO(
                userId: $application->getUserId(),
                type: NotificationTypeEnum::APPLICATION_ACCEPTED,
                title: 'Tavs pieteikums tika pieņemts!',
                body: 'Tavs pieteikums uz "' . $job->getTitle() . '" tika pieņemts. Klients gaida maksājumu.',
                actionUrl: route('jobs.show', $job->getId()),
                metadata: ['job_request_id' => $job->getId(), 'application_id' => $application->getId()],
            ));

            return $job->fresh(['user', 'master', 'escrowHold', 'acceptedApplication']);
        });
    }

    public function initiatePayment(PayJobDTO $dto): string
    {
        $job = $this->jobRequestRepo->findById($dto->jobRequestId);
        abort_if(!$job, 404);

        if ($job->getUserId() !== $dto->clientId) {
            abort(403, 'Šis darbs nav tavs.');
        }

        $this->stateMachine->assertCanTransition($job->getStatus(), JobStatusEnum::IN_PROGRESS);

        $session = $this->stripeCheckout->createSession(new CreateCheckoutSessionDTO(
            jobRequestId: $job->getId(),
            clientId:     $job->getUserId(),
            masterId:     $job->getMasterId(),
            amount:       (float) ($job->getAgreedPrice() ?? 0),
            jobTitle:     $job->getTitle(),
            successUrl:   route('jobs.payment.success', $job->getId()),
            cancelUrl:    route('seeker.job-requests.show', $job->getId()),
        ));

        return $session->url;
    }

    public function confirmPaymentReceived(int $jobRequestId, string $stripeSessionId, float $amountReceived): void
    {
        DB::transaction(function () use ($jobRequestId, $stripeSessionId, $amountReceived): void {
            $job = JobRequest::lockForUpdate()->findOrFail($jobRequestId);

            if ($job->getStatus() === JobStatusEnum::IN_PROGRESS) {
                return;
            }

            $this->stateMachine->assertCanTransition($job->getStatus(), JobStatusEnum::IN_PROGRESS);

            $this->escrowRepo->create([
                EscrowHold::JOB_REQUEST_ID    => $job->getId(),
                EscrowHold::CLIENT_ID         => $job->getUserId(),
                EscrowHold::MASTER_ID         => $job->getMasterId(),
                EscrowHold::AMOUNT            => $amountReceived,
                EscrowHold::STATUS            => EscrowStatusEnum::HELD->value,
                EscrowHold::PAYMENT_REFERENCE => $stripeSessionId,
                EscrowHold::HELD_AT           => Carbon::now(),
            ]);

            $this->jobRequestRepo->updateStatus($job->getId(), JobStatusEnum::IN_PROGRESS);

            $this->notificationService->create(new CreateNotificationDTO(
                userId: $job->getMasterId(),
                type: NotificationTypeEnum::JOB_PAID,
                title: 'Klients samaksāja — vari sākt darbu!',
                body: '"' . $job->getTitle() . '" ir gatavs sākšanai.',
                actionUrl: route('jobs.show', $job->getId()),
                metadata: ['job_request_id' => $job->getId()],
            ));
        });
    }

    public function markComplete(MarkJobCompleteDTO $dto): JobRequest
    {
        $job = $this->jobRequestRepo->findById($dto->jobRequestId);
        abort_if(!$job, 404);

        if ($job->getMasterId() !== $dto->masterId) {
            abort(403, 'Tu neesi šī darba meistars.');
        }

        $this->stateMachine->assertCanTransition($job->getStatus(), JobStatusEnum::AWAITING_CONFIRMATION);

        $this->jobRequestRepo->setMasterCompletedAt($job->getId());

        $escrow = $this->escrowRepo->findByJobRequest($job->getId());
        if ($escrow) {
            $this->escrowRepo->setAutoReleaseAt($escrow->getId(), Carbon::now()->addDays(7));
        }

        $this->notificationService->create(new CreateNotificationDTO(
            userId: $job->getUserId(),
            type: NotificationTypeEnum::JOB_MARKED_COMPLETE,
            title: 'Meistars atzīmēja darbu kā pabeigtu',
            body: '"' . $job->getTitle() . '" gaida tavu apstiprinājumu. Ja 7 dienās neapstiprini, nauda tiks automātiski atbrīvota.',
            actionUrl: route('seeker.job-requests.index'),
            metadata: ['job_request_id' => $job->getId()],
        ));

        return $job->fresh(['user', 'master', 'escrowHold']);
    }

    public function confirmComplete(ConfirmJobDTO $dto): JobRequest
    {
        $job = $this->jobRequestRepo->findById($dto->jobRequestId);
        abort_if(!$job, 404);

        if ($job->getUserId() !== $dto->clientId) {
            abort(403, 'Šis darbs nav tavs.');
        }

        $this->stateMachine->assertCanTransition($job->getStatus(), JobStatusEnum::COMPLETED);

        $this->jobRequestRepo->setCompletedAt($job->getId());
        $this->escrowService->release($job->getId());

        $this->notificationService->create(new CreateNotificationDTO(
            userId: $job->getMasterId(),
            type: NotificationTypeEnum::JOB_CONFIRMED,
            title: 'Klients apstiprināja darbu!',
            body: '"' . $job->getTitle() . '" ir pabeigts. Maksājums ir atbrīvots.',
            actionUrl: route('master.applications.index'),
            metadata: ['job_request_id' => $job->getId()],
        ));

        return $job->fresh(['user', 'master', 'escrowHold']);
    }

    public function dispute(DisputeJobDTO $dto): JobRequest
    {
        $job = $this->jobRequestRepo->findById($dto->jobRequestId);
        abort_if(!$job, 404);

        $isClient = $job->getUserId() === $dto->userId;
        $isMaster = $job->getMasterId() === $dto->userId;

        if (!$isClient && !$isMaster) {
            abort(403, 'Tu neesi šī darba dalībnieks.');
        }

        $this->stateMachine->assertCanTransition($job->getStatus(), JobStatusEnum::DISPUTED);

        $this->jobRequestRepo->updateStatus($job->getId(), JobStatusEnum::DISPUTED);
        $this->disputeRepo->create($job->getId(), $dto->userId, $dto->reason);

        $notifyUserId = $isClient ? $job->getMasterId() : $job->getUserId();
        if ($notifyUserId) {
            $this->notificationService->create(new CreateNotificationDTO(
                userId: $notifyUserId,
                type: NotificationTypeEnum::JOB_DISPUTED,
                title: 'Darbs ir strīdā',
                body: '"' . $job->getTitle() . '" — otra puse atvēra strīdu. Mēs ar jums sazināsimies.',
                actionUrl: route('jobs.show', $job->getId()),
                metadata: ['job_request_id' => $job->getId()],
            ));
        }

        // TODO: notify admin/moderators about the new dispute

        return $job->fresh(['user', 'master', 'escrowHold']);
    }

    public function cancel(CancelJobDTO $dto): JobRequest
    {
        $job = $this->jobRequestRepo->findById($dto->jobRequestId);
        abort_if(!$job, 404);

        $isClient = $job->getUserId() === $dto->userId;
        $isMaster = $job->getMasterId() === $dto->userId;

        if (!$isClient && !$isMaster) {
            abort(403, 'Tu neesi šī darba dalībnieks.');
        }

        $this->stateMachine->assertCanTransition($job->getStatus(), JobStatusEnum::CANCELLED);

        if ($job->getStatus() === JobStatusEnum::ACCEPTED) {
            $this->escrowService->refund($job->getId());
        }

        $this->jobRequestRepo->updateStatus($job->getId(), JobStatusEnum::CANCELLED);

        $notifyUserId = $isClient ? $job->getMasterId() : $job->getUserId();
        if ($notifyUserId) {
            $this->notificationService->create(new CreateNotificationDTO(
                userId: $notifyUserId,
                type: NotificationTypeEnum::JOB_CANCELLED,
                title: 'Darbs tika atcelts',
                body: '"' . $job->getTitle() . '" ir atcelts.',
                actionUrl: route('jobs.show', $job->getId()),
                metadata: ['job_request_id' => $job->getId()],
            ));
        }

        return $job->fresh(['user', 'master', 'escrowHold']);
    }

    public function autoReleaseExpired(): int
    {
        $holds = $this->escrowRepo->getDueForAutoRelease();
        $count = 0;

        foreach ($holds as $hold) {
            $job = $this->jobRequestRepo->findById($hold->getJobRequestId());
            if (!$job || $job->getStatus() !== JobStatusEnum::AWAITING_CONFIRMATION) {
                continue;
            }

            $this->jobRequestRepo->setCompletedAt($job->getId());
            $this->escrowRepo->markReleased($hold->getId());

            $this->notificationService->create(new CreateNotificationDTO(
                userId: $job->getMasterId(),
                type: NotificationTypeEnum::JOB_AUTO_RELEASED,
                title: 'Maksājums automātiski atbrīvots',
                body: '"' . $job->getTitle() . '" — 7 dienu periods beidzās, nauda atbrīvota.',
                actionUrl: route('jobs.show', $job->getId()),
                metadata: ['job_request_id' => $job->getId()],
            ));

            $this->notificationService->create(new CreateNotificationDTO(
                userId: $job->getUserId(),
                type: NotificationTypeEnum::JOB_AUTO_RELEASED,
                title: 'Darbs automātiski apstiprināts',
                body: '"' . $job->getTitle() . '" — apstiprinājuma termiņš beidzās, darbs atzīmēts kā pabeigts.',
                actionUrl: route('jobs.show', $job->getId()),
                metadata: ['job_request_id' => $job->getId()],
            ));

            $count++;
        }

        return $count;
    }
}
