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
    private const MSG_CREATED = 'Pieteikums veiksmīgi iesniegts!';
    private const MSG_CANCELLED = 'Pieteikums atcelts.';
    private const KEY_MESSAGE = 'message';
    private const KEY_DATA = 'data';

    public function __construct(
        private readonly ServiceApplicationLogicRepository $logicRepository
    ) {
    }

    public function store(SaveServiceApplicationRequest $request): JsonResponse
    {
        $application = $this->logicRepository->createApplication($request->toDTO());

        return response()->json([
            self::KEY_MESSAGE => self::MSG_CREATED,
            self::KEY_DATA => new ServiceApplicationResource($application),
        ], 201);
    }

    public function destroy(int $application): JsonResponse
    {
        $this->logicRepository->cancelApplication($application, auth()->id());

        return response()->json([
            self::KEY_MESSAGE => self::MSG_CANCELLED,
        ]);
    }
}
