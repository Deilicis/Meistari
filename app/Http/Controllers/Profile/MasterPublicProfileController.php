<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class MasterPublicProfileController extends Controller
{
    public function show(int $id): Response
    {
        $user = User::with(['profile'])->findOrFail($id);

        $reviews = Review::where(Review::REVIEWEE_ID, $id)
            ->with(['reviewer.profile'])
            ->orderByDesc(Review::CREATED_AT)
            ->get();

        $displayable = $reviews->filter(fn(Review $r) => $r->reviewer !== null)->values();

        return Inertia::render('Public/MasterProfile', [
            'master' => [
                'id' => $user->id,
                'name' => $user->name,
                'profile' => $user->profile,
            ],
            'reviews' => $displayable->map(fn(Review $r) => [
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
            'avg_rating' => $displayable->isNotEmpty() ? round($displayable->avg(Review::RATING), 1) : null,
            'review_count' => $displayable->count(),
        ]);
    }
}
