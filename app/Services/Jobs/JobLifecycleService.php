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
        private readonly JobStateMachine $stateMachine,
        private readonly JobRequestRepository $jobRequestRepo,
        private readonly ApplicationDbRepository $applicationRepo,
        private readonly EscrowHoldRepository $escrowRepo,
        private readonly JobDisputeRepository $disputeRepo,
        private readonly EscrowService $escrowService,
        private readonly NotificationService $notificationService,
        private readonly StripeCheckoutService $stripeCheckout,
    ) {}

    /**
     * Pieņem pieteikumu no meistara un pāriet darbu stāvoklī "accepted".
     * Pēc šī izsaukuma klients ir vienojies ar konkrēto meistaru un nākamais
     * solis ir maksājums.
     */
    public function acceptApplication(AcceptApplicationDTO $dto): JobRequest
    {
        return DB::transaction(function () use ($dto): JobRequest {
            // lockForUpdate novērš sacensību ar paralēliem pieprasījumiem -
            // ja divi klienti vienlaikus mēģinātu pieņemt pieteikumus uz
            // to pašu darbu, otrais gaidītu, kamēr pirmais pabeidz.
            $job = JobRequest::lockForUpdate()->findOrFail($dto->jobRequestId);

            if ($job->getUserId() !== $dto->clientId) {
                abort(403, 'Šis darbs nav tavs.');
            }

            // Aplikācijai jāpieder tieši šim darbam. Sargs pret URL manipulāciju.
            $application = Application::where(Application::ID, $dto->applicationId)
                ->where(Application::JOB_REQUEST_ID, $dto->jobRequestId)
                ->firstOrFail();

            // State Machine pārbauda, vai pāreja atļauta no pašreizējā
            // statusa. Ja darbs jau ir, piem., "in_progress", izmet error.
            $this->stateMachine->assertCanTransition($job->getStatus(), JobStatusEnum::ACCEPTED);

            // Atjauno darbu: piesaista pieņemto aplikāciju, meistaru un cenu.
            // Cena tiek ņemta no aplikācijas piedāvājuma; ja tā nav norādīta,
            // izmanto klienta sākotnējo budžetu kā rezerves vērtību.
            $this->jobRequestRepo->setAcceptedApplication(
                $job->getId(),
                $application->getId(),
                $application->getUserId(),
                (float) ($application->getPriceOffer() ?? $job->getBudget() ?? 0),
                'fixed',
            );

            // Pati aplikācija pāriet statusā "accepted", pārējās aplikācijas
            // uz šo darbu tiek automātiski noraidītas.
            $this->applicationRepo->accept($application);
            $this->applicationRepo->rejectAllPendingForJob($job->getId(), $application->getId());

            // Informē izvēlēto meistaru, ka viņa pieteikums tika pieņemts.
            $this->notificationService->create(new CreateNotificationDTO(
                userId: $application->getUserId(),
                type: NotificationTypeEnum::APPLICATION_ACCEPTED,
                title: 'Tavs pieteikums tika pieņemts!',
                body: 'Tavs pieteikums uz "' . $job->getTitle() . '" tika pieņemts. Klients gaida maksājumu.',
                actionUrl: route('jobs.show', $job->getId()),
                metadata: ['job_request_id' => $job->getId(), 'application_id' => $application->getId()],
            ));

            // Atgriež atjaunoto modeli ar saistītajām relācijām.
            return $job->fresh(['user', 'master', 'escrowHold', 'acceptedApplication']);
        });
    }

    //Uzsāk Stripe Checkout maksājuma sesiju.
    public function initiatePayment(PayJobDTO $dto): string
    {
        $job = $this->jobRequestRepo->findById($dto->jobRequestId);
        abort_if(!$job, 404);

        // Tikai darba īpašnieks var iniciēt maksājumu.
        if ($job->getUserId() !== $dto->clientId) {
            abort(403, 'Šis darbs nav tavs.');
        }

        // Pārbaude, ka darbs ir gatavs maksājumam.
        $this->stateMachine->assertCanTransition($job->getStatus(), JobStatusEnum::IN_PROGRESS);

        // Sargs pret nepilnīgiem datiem.
        abort_if(!$job->getMasterId(), 422, 'Šim darbam nav norādīts meistars.');

        // Izveido Stripe Checkout sesiju. Veiksmīgas un atceltas maksājuma
        // pāradresācijas URL norāda atpakaļ uz šo platformu.
        $session = $this->stripeCheckout->createSession(new CreateCheckoutSessionDTO(
            jobRequestId: $job->getId(),
            clientId: $job->getUserId(),
            masterId: $job->getMasterId(),
            amount: (float) ($job->getAgreedPrice() ?? 0),
            jobTitle: $job->getTitle(),
            successUrl: route('jobs.payment.success', $job->getId()),
            cancelUrl: route('jobs.show', $job->getId()),
        ));

        return $session->url;
    }

    /**
     * Apstiprina, ka Stripe maksājums ir saņemts, un pāriet darbu stāvoklī
     * "in_progress". Šo metodi izsauc TIKAI Stripe webhook kontrolieris pēc
     * "checkout.session.completed" notikuma. Tas ir vienīgais uzticamais
     * signāls, ka klients tiešām ir samaksājis.
     */
    public function confirmPaymentReceived(int $jobRequestId, string $stripeSessionId, float $amountReceived): void
    {
        DB::transaction(function () use ($jobRequestId, $stripeSessionId, $amountReceived): void {
            $job = JobRequest::lockForUpdate()->findOrFail($jobRequestId);

            // Idempotences sargs - ja darbs jau ir "in_progress", maksājums
            // jau ir apstrādāts, šis ir Stripe atkārtots webhook izsaukums,
            // ko mēs ignorējam.
            if ($job->getStatus() === JobStatusEnum::IN_PROGRESS) {
                return;
            }

            $this->stateMachine->assertCanTransition($job->getStatus(), JobStatusEnum::IN_PROGRESS);

            // Izveido eskrov ierakstu. Nauda formāli "tiek turēta" platformas
            // kontā, līdz klients apstiprina darba pabeigšanu vai notiek
            // automātiska atbrīvošana pēc 7 dienām.
            $this->escrowRepo->create([
                EscrowHold::JOB_REQUEST_ID => $job->getId(),
                EscrowHold::CLIENT_ID => $job->getUserId(),
                EscrowHold::MASTER_ID => $job->getMasterId(),
                EscrowHold::AMOUNT => $amountReceived,
                EscrowHold::STATUS => EscrowStatusEnum::HELD->value,
                EscrowHold::PAYMENT_REFERENCE => $stripeSessionId,
                EscrowHold::HELD_AT => Carbon::now(),
            ]);

            $this->jobRequestRepo->updateStatus($job->getId(), JobStatusEnum::IN_PROGRESS);

            // Paziņo meistaram, ka maksājums saņemts un darbu var sākt.
            $this->notificationService->create(new CreateNotificationDTO(
                userId: $job->getMasterId(),
                type: NotificationTypeEnum::JOB_PAID,
                title: 'Klients samaksāja - vari sākt darbu!',
                body: '"' . $job->getTitle() . '" ir gatavs sākšanai.',
                actionUrl: route('jobs.show', $job->getId()),
                metadata: ['job_request_id' => $job->getId()],
            ));
        });
    }

    /**
     * Meistars atzīmē darbu kā pabeigtu no savas puses. Darbs pāriet
     * stāvoklī "awaiting_confirmation" - klientam tagad ir 7 dienas, lai
     * vai nu apstiprinātu pabeigšanu, vai atvērtu strīdu. Ja klients nereaģē,
     * autoReleaseExpired() vēlāk veiks automātisku atbrīvošanu.
     */
    public function markComplete(MarkJobCompleteDTO $dto): JobRequest
    {
        $job = $this->jobRequestRepo->findById($dto->jobRequestId);
        abort_if(!$job, 404);

        // Tikai piesaistītais meistars var atzīmēt darbu kā pabeigtu.
        if ($job->getMasterId() !== $dto->masterId) {
            abort(403, 'Tu neesi šī darba meistars.');
        }

        $this->stateMachine->assertCanTransition($job->getStatus(), JobStatusEnum::AWAITING_CONFIRMATION);

        $this->jobRequestRepo->setMasterCompletedAt($job->getId());

        // Iestatām automātiskās atbrīvošanas termiņu eskrov ierakstā.
        $escrow = $this->escrowRepo->findByJobRequest($job->getId());
        if ($escrow) {
            $this->escrowRepo->setAutoReleaseAt($escrow->getId(), Carbon::now()->addDays(7));
        }

        $this->notificationService->create(new CreateNotificationDTO(
            userId: $job->getUserId(),
            type: NotificationTypeEnum::JOB_MARKED_COMPLETE,
            title: 'Meistars atzīmēja darbu kā pabeigtu',
            body: '"' . $job->getTitle() . '" gaida tavu apstiprinājumu. Ja 7 dienās neapstiprini, nauda tiks automātiski atbrīvota.',
            actionUrl: route('jobs.show', $job->getId()),
            metadata: ['job_request_id' => $job->getId()],
        ));

        return $job->fresh(['user', 'master', 'escrowHold']);
    }

    /**
     * Klients apstiprina darba pabeigšanu.
     * Darbs tiek atzīmēts kā pabeigts un eskrov nauda tiek atbrīvota
     * meistaram caur EscrowService.
     */
    public function confirmComplete(ConfirmJobDTO $dto): JobRequest
    {
        $job = $this->jobRequestRepo->findById($dto->jobRequestId);
        abort_if(!$job, 404);

        // Tikai darba īpašnieks var apstiprināt pabeigšanu.
        if ($job->getUserId() !== $dto->clientId) {
            abort(403, 'Šis darbs nav tavs.');
        }

        $this->stateMachine->assertCanTransition($job->getStatus(), JobStatusEnum::COMPLETED);

        $this->jobRequestRepo->setCompletedAt($job->getId());

        // Faktiskā eskrov atbrīvošana - mainās statuss uz "released",
        // produkcijas vidē šeit notiktu reāls Stripe pārvedums meistaram.
        $this->escrowService->release($job->getId());

        // Paziņo meistaram, ka maksājums tagad ir atbrīvots.
        if ($masterId = $job->getMasterId()) {
            $this->notificationService->create(new CreateNotificationDTO(
                userId: $masterId,
                type: NotificationTypeEnum::JOB_CONFIRMED,
                title: 'Klients apstiprināja darbu!',
                body: '"' . $job->getTitle() . '" ir pabeigts. Maksājums ir atbrīvots.',
                actionUrl: route('jobs.show', $job->getId()),
                metadata: ['job_request_id' => $job->getId()],
            ));
        }

        return $job->fresh(['user', 'master', 'escrowHold']);
    }

    /**
     * Atver strīdu par darbu. Var izsaukt gan klients, gan meistars, ja
     * viņi nav apmierināti par darba kvalitāti vai pabeigšanu. Darbs
     * pāriet stāvoklī "disputed" un eskrov nauda paliek iesaldēta līdz
     * admina lēmumam.
     */
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

        // Saglabājam pašu strīda ierakstu - iemeslu, iniciatoru, laiku.
        $this->jobRequestRepo->updateStatus($job->getId(), JobStatusEnum::DISPUTED);
        $this->disputeRepo->create($job->getId(), $dto->userId, $dto->reason);

        // Paziņojam otrai pusei - kurš nav iniciators, tas saņem paziņojumu.
        $notifyUserId = $isClient ? $job->getMasterId() : $job->getUserId();
        if ($notifyUserId) {
            $this->notificationService->create(new CreateNotificationDTO(
                userId: $notifyUserId,
                type: NotificationTypeEnum::JOB_DISPUTED,
                title: 'Darbs ir strīdā',
                body: '"' . $job->getTitle() . '" - otra puse atvēra strīdu. Mēs ar jums sazināsimies.',
                actionUrl: route('jobs.show', $job->getId()),
                metadata: ['job_request_id' => $job->getId()],
            ));
        }

        // TODO: paziņot administratoriem par jauno strīdu (e-pasts vai dashboards).

        return $job->fresh(['user', 'master', 'escrowHold']);
    }

    /**
     * Atceļ darbu. Atļauts gan klientam, gan meistaram, bet tikai pirms
     * darbs ir pārgājis stāvoklī "in_progress". Maksājuma atcelšana
     * notiek tikai caur strīda procesu.
     */
    public function cancel(CancelJobDTO $dto): JobRequest
    {
        $job = $this->jobRequestRepo->findById($dto->jobRequestId);
        abort_if(!$job, 404);

        // Atcelt drīkst tikai darba dalībnieki.
        $isClient = $job->getUserId() === $dto->userId;
        $isMaster = $job->getMasterId() === $dto->userId;

        if (!$isClient && !$isMaster) {
            abort(403, 'Tu neesi šī darba dalībnieks.');
        }

        $this->stateMachine->assertCanTransition($job->getStatus(), JobStatusEnum::CANCELLED);

        // Ja maksājums jau bija veikts (stāvoklis "accepted" pēc maksājuma),
        // veicam atmaksu klientam pirms darba atcelšanas.
        if ($job->getStatus() === JobStatusEnum::ACCEPTED) {
            $this->escrowService->refund($job->getId());
        }

        $this->jobRequestRepo->updateStatus($job->getId(), JobStatusEnum::CANCELLED);

        // Paziņojam otrai pusei par atcelšanu.
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

    /**
     * Automātiski atbrīvo eskrov naudu darbiem, kuri 7 dienas atrodas
     * stāvoklī "awaiting_confirmation" un klients nav reaģējis.
     */
    public function autoReleaseExpired(): int
    {
        $holds = $this->escrowRepo->getDueForAutoRelease();
        $count = 0;

        foreach ($holds as $hold) {
            $job = $this->jobRequestRepo->findById($hold->getJobRequestId());

            // Pārliecināmies, ka darbs joprojām ir gaidīšanas stāvoklī.
            if (!$job || $job->getStatus() !== JobStatusEnum::AWAITING_CONFIRMATION) {
                continue;
            }

            $this->jobRequestRepo->setCompletedAt($job->getId());
            $this->escrowRepo->markReleased($hold->getId());

            // Paziņojam meistaram par automātisko atbrīvošanu.
            if ($masterId = $job->getMasterId()) {
                $this->notificationService->create(new CreateNotificationDTO(
                    userId: $masterId,
                    type: NotificationTypeEnum::JOB_AUTO_RELEASED,
                    title: 'Maksājums automātiski atbrīvots',
                    body: '"' . $job->getTitle() . '" - 7 dienu periods beidzās, nauda atbrīvota.',
                    actionUrl: route('jobs.show', $job->getId()),
                    metadata: ['job_request_id' => $job->getId()],
                ));
            }

            // Paziņojam arī klientam - viņš varēja vienkārši aizmirst
            // apstiprināt, tāpēc tas nav uzbrūkošs paziņojums.
            $this->notificationService->create(new CreateNotificationDTO(
                userId: $job->getUserId(),
                type: NotificationTypeEnum::JOB_AUTO_RELEASED,
                title: 'Darbs automātiski apstiprināts',
                body: '"' . $job->getTitle() . '" - apstiprinājuma termiņš beidzās, darbs atzīmēts kā pabeigts.',
                actionUrl: route('jobs.show', $job->getId()),
                metadata: ['job_request_id' => $job->getId()],
            ));

            $count++;
        }

        return $count;
    }