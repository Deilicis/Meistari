<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\Proposals\CounterProposalDTO;
use App\DTOs\Proposals\RespondToProposalDTO;
use App\DTOs\Proposals\SubmitProposalDTO;
use App\Http\Requests\Proposals\CounterProposalRequest;
use App\Http\Requests\Proposals\SubmitProposalRequest;
use App\Http\Resources\PriceProposalResource;
use App\Services\Proposals\PriceProposalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceProposalController extends Controller
{
    public function __construct(
        private readonly PriceProposalService $service,
    ) {}

    public function store(SubmitProposalRequest $request, int $applicationId): JsonResponse
    {
        $proposal = $this->service->submitFreshProposal(new SubmitProposalDTO(
            jobApplicationId: $applicationId,
            proposedByUserId: auth()->id(),
            amount:           (float) $request->validated('amount'),
            note:             $request->validated('note'),
        ));

        return response()->json([
            'message' => 'Piedāvājums nosūtīts.',
            'data'    => new PriceProposalResource($proposal->load(['proposedBy', 'respondedBy'])),
        ], 201);
    }

    public function counter(CounterProposalRequest $request, int $proposalId): JsonResponse
    {
        $proposal = $this->service->counter(new CounterProposalDTO(
            currentProposalId: $proposalId,
            counteredByUserId: auth()->id(),
            newAmount:         (float) $request->validated('amount'),
            newNote:           $request->validated('note'),
        ));

        return response()->json([
            'message' => 'Pretpiedāvājums nosūtīts.',
            'data'    => new PriceProposalResource($proposal->load(['proposedBy', 'respondedBy'])),
        ]);
    }

    public function accept(Request $request, int $proposalId): JsonResponse
    {
        $proposal = $this->service->accept(new RespondToProposalDTO(
            proposalId:        $proposalId,
            respondedByUserId: auth()->id(),
        ));

        return response()->json([
            'message' => 'Piedāvājums pieņemts!',
            'data'    => new PriceProposalResource($proposal->load(['proposedBy', 'respondedBy'])),
        ]);
    }

    public function reject(Request $request, int $proposalId): JsonResponse
    {
        $proposal = $this->service->reject(new RespondToProposalDTO(
            proposalId:        $proposalId,
            respondedByUserId: auth()->id(),
        ));

        return response()->json([
            'message' => 'Piedāvājums noraidīts.',
            'data'    => new PriceProposalResource($proposal->load(['proposedBy', 'respondedBy'])),
        ]);
    }

    public function withdraw(Request $request, int $proposalId): JsonResponse
    {
        $proposal = $this->service->withdraw(new RespondToProposalDTO(
            proposalId:        $proposalId,
            respondedByUserId: auth()->id(),
        ));

        return response()->json([
            'message' => 'Piedāvājums atsaukts.',
            'data'    => new PriceProposalResource($proposal->load(['proposedBy', 'respondedBy'])),
        ]);
    }
}
