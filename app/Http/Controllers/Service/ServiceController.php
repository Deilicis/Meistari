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
    private const MSG_CREATED = 'Pakalpojums veiksmīgi izveidots!';
    private const MSG_UPDATED = 'Pakalpojums atjaunināts!';
    private const MSG_DELETED = 'Pakalpojums izdzēsts!';
    private const KEY_MESSAGE = 'message';
    private const KEY_DATA = 'data';

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
            self::KEY_MESSAGE => self::MSG_CREATED,
            self::KEY_DATA => new ServiceResource($service),
        ], 201);
    }

    public function update(SaveServiceRequest $request, Service $service): JsonResponse
    {
        $this->logicRepository->updateService($service, $request->toDTO(), $request->user()->id);
        $service->refresh();

        return response()->json([
            self::KEY_MESSAGE => self::MSG_UPDATED,
            self::KEY_DATA => new ServiceResource($service),
        ], 200);
    }

    public function destroy(Request $request, Service $service): JsonResponse
    {
        $this->logicRepository->deleteService($service, $request->user()->id);

        return response()->json([
            self::KEY_MESSAGE => self::MSG_DELETED,
        ], 200);
    }
}
