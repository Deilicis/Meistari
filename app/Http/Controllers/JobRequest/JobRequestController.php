<?php

declare(strict_types=1);

namespace App\Http\Controllers\JobRequest;

use App\Http\Requests\JobRequest\SaveJobRequest;
use App\Http\Resources\JobRequestResource;
use App\Models\JobRequest;
use App\Http\Controllers\Controller;
use App\Services\Repositories\JobRequest\JobRequestLogicRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobRequestController extends Controller
{
    public function __construct(
        private readonly JobRequestLogicRepository $logicRepository
    ) {
    }

    public function apiIndex(): JsonResponse
    {
        $jobs = $this->logicRepository->getPaginatedJobRequests();
        
        return JobRequestResource::collection($jobs)->response();
    }

    public function apiMyRequests(Request $request): JsonResponse
    {
        $jobs = $this->logicRepository->getUserJobRequests($request->user()->id);
        
        return response()->json(JobRequestResource::collection($jobs));
    }

    public function apiShow(int $id): JsonResponse
    {
        $job = $this->logicRepository->getJobRequestById($id);
        
        return response()->json(new JobRequestResource($job));
    }

    public function apiStore(SaveJobRequest $request): JsonResponse
    {
        $job = $this->logicRepository->createJobRequest($request->toDTO());

        return response()->json([
            'message' => 'Darba sludinājums veiksmīgi publicēts!',
            'data' => new JobRequestResource($job),
        ], 201);
    }

    public function apiUpdate(SaveJobRequest $request, JobRequest $jobRequest): JsonResponse
    {
        $this->logicRepository->updateJobRequest($jobRequest, $request->toDTO(), $request->user()->id);
        $jobRequest->refresh();

        return response()->json([
            'message' => 'Darba sludinājums veiksmīgi atjaunināts!',
            'data' => new JobRequestResource($jobRequest),
        ], 200);
    }

    public function apiDestroy(Request $request, JobRequest $jobRequest): JsonResponse
    {
        $this->logicRepository->deleteJobRequest($jobRequest, $request->user()->id);

        return response()->json([
            'message' => 'Darba sludinājums veiksmīgi izdzēsts!'
        ], 200);
    }
}