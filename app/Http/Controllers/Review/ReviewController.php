<?php

declare(strict_types=1);

namespace App\Http\Controllers\Review;

use App\Http\Controllers\Controller;
use App\Services\Repositories\Review\ReviewLogicRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    private const MSG_CREATED = 'Atsauksme veiksmīgi pievienota!';

    public function __construct(
        private readonly ReviewLogicRepository $logicRepository
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_request_id' => ['required', 'integer', 'exists:job_requests,id'],
            'reviewee_id'    => ['required', 'integer', 'exists:users,id'],
            'rating'         => ['required', 'integer', 'min:1', 'max:5'],
            'comment'        => ['nullable', 'string', 'max:1000'],
        ]);

        $review = $this->logicRepository->createReview(
            jobRequestId: $validated['job_request_id'],
            reviewerId:   Auth::id(),
            revieweeId:   $validated['reviewee_id'],
            rating:       $validated['rating'],
            comment:      $validated['comment'] ?? null,
        );

        return response()->json(['message' => self::MSG_CREATED, 'data' => ['id' => $review->getId()]], 201);
    }
}
