<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\JobRequest;
use App\Models\Review;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class SeekerPublicProfileController extends Controller
{
    public function show(int $id): Response
    {
        $user = User::with(['profile'])->findOrFail($id);

        $reviews = Review::where(Review::REVIEWEE_ID, $id)
            ->with(['reviewer.profile'])
            ->orderByDesc(Review::CREATED_AT)
            ->get();

        $avgRating = $reviews->isNotEmpty() ? round($reviews->avg(Review::RATING), 1) : null;

        $jobRequests = JobRequest::where(JobRequest::USER_ID, $id)
            ->where(JobRequest::STATUS, 'active')
            ->with(['category'])
            ->orderByDesc(JobRequest::CREATED_AT)
            ->limit(5)
            ->get();

        return Inertia::render('Public/SeekerProfile', [
            'seeker' => [
                'id' => $user->id,
                'name' => $user->name,
                'profile' => $user->profile,
            ],
            'reviews' => $reviews->map(fn(Review $r) => [
                'id' => $r->getId(),
                'rating' => $r->getRating(),
                'comment' => $r->getComment(),
                'created_at' => $r->getCreatedAt(),
                'reviewer' => [
                    'id' => $r->reviewer->id,
                    'name' => $r->reviewer->name,
                    'profile' => $r->reviewer->profile,
                ],
            ]),
            'avg_rating' => $avgRating,
            'review_count' => $reviews->count(),
            'job_requests' => $jobRequests->map(fn(JobRequest $jr) => [
                'id' => $jr->getId(),
                'title' => $jr->getTitle(),
                'budget' => $jr->getBudget(),
                'deadline' => $jr->getDeadline(),
                'category' => $jr->category ? ['name' => $jr->category->getName()] : null,
            ]),
        ]);
    }
}
