<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Service\SaveServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Services\Repositories\Service\ServiceLogicRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct(
        private readonly ServiceLogicRepository $logicRepository
    ) {
    }

    public function apiIndex(): JsonResponse
    {
        $services = $this->logicRepository->getPaginatedServices();
        
        return ServiceResource::collection($services)->response();
    }

    public function apiMyServices(Request $request): JsonResponse
    {
        $services = $this->logicRepository->getUserServices($request->user()->id);
        
        return response()->json(ServiceResource::collection($services));
    }

    public function apiShow(int $id): JsonResponse
    {
        $service = $this->logicRepository->getServiceById($id);
        
        return response()->json(new ServiceResource($service));
    }

    public function apiStore(SaveServiceRequest $request): JsonResponse
    {
        $service = $this->logicRepository->createService($request->toDTO());

        return response()->json([
            'message' => 'Pakalpojums veiksmīgi izveidots!',
            'data' => new ServiceResource($service),
        ], 201);
    }

    public function apiUpdate(SaveServiceRequest $request, Service $service): JsonResponse
    {
        $this->logicRepository->updateService($service, $request->toDTO(), $request->user()->id);

        $service->refresh();

        return response()->json([
            'message' => 'Pakalpojums veiksmīgi atjaunināts!',
            'data' => new ServiceResource($service),
        ], 200);
    }

    public function apiDestroy(Request $request, Service $service): JsonResponse
    {
        $this->logicRepository->deleteService($service, $request->user()->id);

        return response()->json([
            'message' => 'Pakalpojums veiksmīgi izdzēsts!'
        ], 200);
    }
}