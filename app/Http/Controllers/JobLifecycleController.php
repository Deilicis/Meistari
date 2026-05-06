<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\Jobs\AcceptApplicationDTO;
use App\DTOs\Jobs\CancelJobDTO;
use App\DTOs\Jobs\ConfirmJobDTO;
use App\DTOs\Jobs\DisputeJobDTO;
use App\DTOs\Jobs\MarkJobCompleteDTO;
use App\DTOs\Jobs\PayJobDTO;
use App\Http\Requests\Jobs\AcceptApplicationRequest;
use App\Http\Requests\Jobs\CancelJobRequest;
use App\Http\Requests\Jobs\DisputeJobRequest;
use App\Http\Requests\Jobs\MarkJobCompleteRequest;
use App\Http\Requests\Jobs\PayJobRequest;
use App\Http\Resources\JobLifecycleResource;
use App\Services\Jobs\JobLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobLifecycleController extends Controller
{
    public function __construct(
        private readonly JobLifecycleService $service,
    ) {}

    public function acceptApplication(AcceptApplicationRequest $request, int $jobId): JsonResponse
    {
        $job = $this->service->acceptApplication(new AcceptApplicationDTO(
            jobRequestId: $jobId,
            applicationId: $request->integer('application_id'),
            clientId: $request->user()->getId(),
        ));

        return response()->json(new JobLifecycleResource($job));
    }

    public function pay(PayJobRequest $request, int $jobId): JsonResponse
    {
        $url = $this->service->initiatePayment(new PayJobDTO(
            jobRequestId: $jobId,
            clientId: $request->user()->getId(),
        ));

        return response()->json(['url' => $url]);
    }

    public function markComplete(MarkJobCompleteRequest $request, int $jobId): JsonResponse
    {
        $job = $this->service->markComplete(new MarkJobCompleteDTO(
            jobRequestId: $jobId,
            masterId: $request->user()->getId(),
            completionNote: $request->string('completion_note')->value() ?: null,
        ));

        return response()->json(new JobLifecycleResource($job));
    }

    public function confirm(Request $request, int $jobId): JsonResponse
    {
        $job = $this->service->confirmComplete(new ConfirmJobDTO(
            jobRequestId: $jobId,
            clientId: $request->user()->getId(),
        ));

        return response()->json(new JobLifecycleResource($job));
    }

    public function dispute(DisputeJobRequest $request, int $jobId): JsonResponse
    {
        $job = $this->service->dispute(new DisputeJobDTO(
            jobRequestId: $jobId,
            userId: $request->user()->getId(),
            reason: $request->string('reason')->value(),
        ));

        return response()->json(new JobLifecycleResource($job));
    }

    public function cancel(CancelJobRequest $request, int $jobId): JsonResponse
    {
        $job = $this->service->cancel(new CancelJobDTO(
            jobRequestId: $jobId,
            userId: $request->user()->getId(),
            reason: $request->string('reason')->value() ?: null,
        ));

        return response()->json(new JobLifecycleResource($job));
    }
}
