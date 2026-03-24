<?php

declare(strict_types=1);

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Http\Requests\Application\SaveApplicationRequest;
use App\Http\Resources\ApplicationResource;
use App\Services\Repositories\Application\ApplicationLogicRepository;
use Illuminate\Http\JsonResponse;

class ApplicationController extends Controller
{
    private const MSG_CREATED   = 'Pieteikums veiksmīgi iesniegts!';
    private const MSG_CANCELLED = 'Pieteikums atcelts.';
    private const KEY_MESSAGE   = 'message';
    private const KEY_DATA      = 'data';

    public function __construct(
        private readonly ApplicationLogicRepository $logicRepository
    ) {}

    public function store(SaveApplicationRequest $request): JsonResponse
    {
        $application = $this->logicRepository->createApplication($request->toDTO());

        return response()->json([
            self::KEY_MESSAGE => self::MSG_CREATED,
            self::KEY_DATA    => new ApplicationResource($application),
        ], 201);
    }

    public function destroy(int $application): JsonResponse
    {
        $this->logicRepository->cancelApplication($application, auth()->id());

        return response()->json([self::KEY_MESSAGE => self::MSG_CANCELLED]);
    }
}
