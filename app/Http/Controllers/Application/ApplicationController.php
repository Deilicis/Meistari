<?php

declare(strict_types=1);

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Http\Requests\Application\SaveApplicationRequest;
use App\Http\Resources\ApplicationResource;
use App\Http\Resources\JobApplicationResource;
use App\Services\Repositories\Application\ApplicationLogicRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    private const MSG_CREATED = 'Pieteikums veiksmīgi iesniegts!';
    private const MSG_CANCELLED = 'Pieteikums atcelts.';
    private const MSG_ACCEPTED = 'Pieteikums pieņemts!';
    private const MSG_REJECTED = 'Pieteikums noraidīts.';
    private const KEY_MESSAGE = 'message';
    private const KEY_DATA = 'data';

    public function __construct(
        private readonly ApplicationLogicRepository $logicRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate(['job_request_id' => ['required', 'integer']]);
        $applications = $this->logicRepository->getJobApplications(
            (int) $request->query('job_request_id'),
            auth()->id()
        );

        return response()->json(['data' => JobApplicationResource::collection($applications)->resolve()]);
    }

    public function store(SaveApplicationRequest $request): JsonResponse
    {
        $application = $this->logicRepository->createApplication($request->toDTO());

        return response()->json([
            self::KEY_MESSAGE => self::MSG_CREATED,
            self::KEY_DATA => new ApplicationResource($application),
        ], 201);
    }

    public function shortlist(int $application): JsonResponse
    {
        $shortlisted = $this->logicRepository->shortlistApplication($application, auth()->id());

        return response()->json([
            self::KEY_MESSAGE => 'Pieteikums iekļauts īsajā sarakstā!',
            self::KEY_DATA => new JobApplicationResource($shortlisted),
        ]);
    }

    public function accept(int $application): JsonResponse
    {
        $accepted = $this->logicRepository->acceptApplication($application, auth()->id());

        return response()->json([
            self::KEY_MESSAGE => self::MSG_ACCEPTED,
            self::KEY_DATA => new JobApplicationResource($accepted),
        ]);
    }

    public function reject(int $application): JsonResponse
    {
        $rejected = $this->logicRepository->rejectApplication($application, auth()->id());

        return response()->json([
            self::KEY_MESSAGE => self::MSG_REJECTED,
            self::KEY_DATA => new JobApplicationResource($rejected),
        ]);
    }

    public function destroy(int $application): JsonResponse
    {
        $this->logicRepository->cancelApplication($application, auth()->id());

        return response()->json([self::KEY_MESSAGE => self::MSG_CANCELLED]);
    }
}
