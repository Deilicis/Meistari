<?php

declare(strict_types=1);

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
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

    public function index(): JsonResponse
    {
        $services = $this->logicRepository->getPaginatedServices();
        return ServiceResource::collection($services)->response();
    }

    public function myServices(Request $request): JsonResponse
    {
        $services = $this->logicRepository->getUserServices($request->user()->id);
        return response()->json(ServiceResource::collection($services));
    }

    public function show(int $id): JsonResponse
    {
        $service = $this->logicRepository->getServiceById($id);
        return response()->json(new ServiceResource($service));
    }

    public function store(SaveServiceRequest $request): JsonResponse
    {
        $service = $this->logicRepository->createService($request->toDTO());

        return response()->json([
            'message' => 'Pakalpojums veiksmīgi izveidots!',
            'data' => new ServiceResource($service),
        ], 201);
    }

    public function update(SaveServiceRequest $request, Service $service): JsonResponse
    {
        $this->logicRepository->updateService($service, $request->toDTO(), $request->user()->id);
        $service->refresh();

        return response()->json([
            'message' => 'Pakalpojums atjaunināts!',
            'data' => new ServiceResource($service),
        ], 200);
    }

    public function destroy(Request $request, Service $service): JsonResponse
    {
        $this->logicRepository->deleteService($service, $request->user()->id);

        return response()->json([
            'message' => 'Pakalpojums izdzēsts!'
        ], 200);
    }
}