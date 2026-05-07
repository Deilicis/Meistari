<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\DTOs\Notifications\CreateNotificationDTO;
use App\Enums\Complaint\ComplaintStatusEnum;
use App\Enums\Job\JobStatusEnum;
use App\Enums\NotificationTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateComplaintRequest;
use App\Models\Complaint;
use App\Models\JobRequest;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ComplaintController extends Controller
{
    private const MSG_UPDATED   = 'Sūdzība atjaunināta.';
    private const FLASH_SUCCESS = 'success';

    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function index(Request $request): Response
    {
        $query = Complaint::with(['reporter:id,name', 'reportedUser:id,name', 'resolvedBy:id,name']);

        if ($status = $request->get('status')) {
            $query->where(Complaint::STATUS, $status);
        }

        return Inertia::render('Admin/Complaints/Index', [
            'complaints' => $query->latest()->paginate(20)->withQueryString(),
            'filters'    => $request->only(['status']),
            'statuses'   => ComplaintStatusEnum::cases(),
        ]);
    }

    public function show(Complaint $complaint): Response
    {
        $complaint->load(['reporter.profile', 'reportedUser.profile', 'resolvedBy.profile', 'reportedEntity']);

        return Inertia::render('Admin/Complaints/Show', [
            'complaint' => $complaint,
        ]);
    }

    public function update(UpdateComplaintRequest $request, Complaint $complaint): RedirectResponse
    {
        $status = ComplaintStatusEnum::from($request->validated(Complaint::STATUS));

        $data = [
            Complaint::STATUS          => $status->value,
            Complaint::RESOLUTION_NOTE => $request->validated(Complaint::RESOLUTION_NOTE),
        ];

        if (in_array($status, [ComplaintStatusEnum::RESOLVED, ComplaintStatusEnum::DISMISSED], true)) {
            $data[Complaint::RESOLVED_BY] = auth()->id();
            $data[Complaint::RESOLVED_AT] = now();
        }

        DB::transaction(function () use ($complaint, $data, $status): void {
            $complaint->update($data);

            if ($status === ComplaintStatusEnum::RESOLVED) {
                $this->handleResolvedComplaint($complaint);
            }
        });

        return back()->with(self::FLASH_SUCCESS, self::MSG_UPDATED);
    }

    private function handleResolvedComplaint(Complaint $complaint): void
    {
        $complaint->load('reportedEntity');
        $entity = $complaint->reportedEntity;

        if (!($entity instanceof JobRequest)) {
            return;
        }

        if ($entity->getStatus() !== JobStatusEnum::DISPUTED) {
            return;
        }

        $masterId  = $entity->getMasterId();
        $clientId  = $entity->getUserId();
        $jobTitle  = $entity->getTitle();
        $jobId     = $entity->getId();

        $entity->update([
            JobRequest::STATUS                  => JobStatusEnum::OPEN->value,
            JobRequest::MASTER_ID               => null,
            JobRequest::ACCEPTED_APPLICATION_ID => null,
            JobRequest::AGREED_PRICE            => null,
            JobRequest::PRICE_TYPE              => null,
            JobRequest::MASTER_COMPLETED_AT     => null,
        ]);

        $this->notificationService->create(new CreateNotificationDTO(
            userId: $clientId,
            type: NotificationTypeEnum::JOB_DISPUTED,
            title: 'Strīds atrisināts — darbs atvērts atkārtoti',
            body: '"' . $jobTitle . '" ir atgriezts atvērtā stāvoklī. Vari pieņemt jaunu meistaru.',
            actionUrl: route('jobs.show', $jobId),
            metadata: ['job_request_id' => $jobId],
        ));

        if ($masterId) {
            $this->notificationService->create(new CreateNotificationDTO(
                userId: $masterId,
                type: NotificationTypeEnum::JOB_DISPUTED,
                title: 'Strīds atrisināts',
                body: '"' . $jobTitle . '" strīds ir slēgts no administrācijas puses.',
                actionUrl: route('jobs.show', $jobId),
                metadata: ['job_request_id' => $jobId],
            ));
        }
    }
}
