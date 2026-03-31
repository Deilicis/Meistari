<?php

declare(strict_types=1);

namespace App\Http\Controllers\Complaint;

use App\Enums\Complaint\ComplaintStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Complaint\StoreComplaintRequest;
use App\Models\Complaint;
use Illuminate\Http\JsonResponse;

class ComplaintController extends Controller
{
    private const MSG_STORED = 'Sūdzība veiksmīgi iesniegta.';

    public function store(StoreComplaintRequest $request): JsonResponse
    {
        $complaint = Complaint::create([
            Complaint::REPORTER_ID          => auth()->id(),
            Complaint::REPORTED_USER_ID     => $request->validated(Complaint::REPORTED_USER_ID),
            Complaint::REPORTED_ENTITY_TYPE => $request->validated(Complaint::REPORTED_ENTITY_TYPE),
            Complaint::REPORTED_ENTITY_ID   => $request->validated(Complaint::REPORTED_ENTITY_ID),
            Complaint::REASON               => $request->validated(Complaint::REASON),
            Complaint::STATUS               => ComplaintStatusEnum::PENDING->value,
        ]);

        return response()->json([
            'message' => self::MSG_STORED,
            'data'    => $complaint,
        ], 201);
    }
}
