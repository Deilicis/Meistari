<?php

declare(strict_types=1);

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceApplication\SaveServiceApplicationRequest;
use App\Http\Resources\ServiceApplicationResource;
use App\Services\Repositories\ServiceApplication\ServiceApplicationLogicRepository;
use Illuminate\Http\JsonResponse;

class ServiceApplicationController extends Controller
{
    public function __construct(
        private readonly ServiceApplicationLogicRepository $logicRepository
    ) {
    }

    public function store(SaveServiceApplicationRequest $request): JsonResponse
    {
        $application = $this->logicRepository->createApplication($request->toDTO());

        return response()->json([
            'message' => 'Pieteikums veiksmīgi iesniegts!',
            'data'    => new ServiceApplicationResource($application),
        ], 201);
    }
}
