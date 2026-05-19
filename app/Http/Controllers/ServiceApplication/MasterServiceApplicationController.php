<?php

declare(strict_types=1);

namespace App\Http\Controllers\ServiceApplication;

use App\Http\Controllers\Controller;
use App\Http\Resources\MasterServiceApplicationResource;
use App\Services\Repositories\ServiceApplication\ServiceApplicationLogicRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MasterServiceApplicationController extends Controller
{
    public function __construct(
        private readonly ServiceApplicationLogicRepository $repo,
    ) {}

    public function index(Request $request): Response
    {
        $applications = $this->repo->getMasterApplications($request->user()->getId());

        return Inertia::render('Master/ServiceApplications/Index', [
            'applications' => MasterServiceApplicationResource::collection($applications),
        ]);
    }

    public function forService(Request $request, int $serviceId): Response
    {
        $applications = $this->repo->getServiceApplications($serviceId, $request->user()->getId());

        return Inertia::render('Master/ServiceApplications/ForService', [
            'applications' => MasterServiceApplicationResource::collection($applications),
            'serviceId' => $serviceId,
        ]);
    }

    public function accept(Request $request, int $applicationId): JsonResponse
    {
        $application = $this->repo->acceptApplication($applicationId, $request->user()->getId());

        return response()->json(new MasterServiceApplicationResource($application));
    }

    public function reject(Request $request, int $applicationId): JsonResponse
    {
        $application = $this->repo->rejectApplication($applicationId, $request->user()->getId());

        return response()->json(new MasterServiceApplicationResource($application));
    }
}
