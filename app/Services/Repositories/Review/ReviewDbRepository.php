<?php

declare(strict_types=1);

namespace App\Services\Repositories\Review;

use App\Models\Review;

class ReviewDbRepository
{
    public function create(array $data): Review
    {
        return Review::create($data);
    }

    public function findByJobAndReviewer(int $jobRequestId, int $reviewerId): ?Review
    {
        return Review::where(Review::JOB_REQUEST_ID, $jobRequestId)
            ->where(Review::REVIEWER_ID, $reviewerId)
            ->first();
    }
}
