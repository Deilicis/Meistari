<?php

declare(strict_types=1);

namespace App\Services\Repositories\Review;

use App\Constants\ErrorMessages;
use App\DTOs\Notifications\CreateNotificationDTO;
use App\Enums\Job\ApplicationStatusEnum;
use App\Enums\Job\JobStatusEnum;
use App\Enums\NotificationTypeEnum;
use App\Models\JobRequest;
use App\Models\Review;
use App\Services\NotificationService;
use App\Services\Repositories\Application\ApplicationDbRepository;

class ReviewLogicRepository
{
    public function __construct(
        private readonly ReviewDbRepository      $dbRepository,
        private readonly ApplicationDbRepository $applicationDbRepository,
        private readonly NotificationService     $notificationService,
    ) {}

    public function createReview(
        int $jobRequestId,
        int $reviewerId,
        int $revieweeId,
        int $rating,
        ?string $comment,
    ): Review {
        $jobRequest = JobRequest::findOrFail($jobRequestId);

        if ($jobRequest->getStatus() !== JobStatusEnum::COMPLETED) {
            abort(422, ErrorMessages::JOB_NOT_COMPLETED);
        }

        $isSeeker = $jobRequest->getUserId() === $reviewerId;

        if (!$isSeeker) {
            $accepted = $this->applicationDbRepository->findAcceptedForJob($jobRequestId);
            $isMaster = $accepted && $accepted->getUserId() === $reviewerId;

            if (!$isMaster) {
                abort(403, ErrorMessages::REVIEW_NOT_PARTICIPANT);
            }
        }

        if ($this->dbRepository->findByJobAndReviewer($jobRequestId, $reviewerId)) {
            abort(422, ErrorMessages::REVIEW_ALREADY_SUBMITTED);
        }

        $review = $this->dbRepository->create([
            Review::JOB_REQUEST_ID => $jobRequestId,
            Review::REVIEWER_ID => $reviewerId,
            Review::REVIEWEE_ID => $revieweeId,
            Review::RATING => $rating,
            Review::COMMENT => $comment,
        ]);

        $this->notificationService->create(new CreateNotificationDTO(
            userId: $revieweeId,
            type: NotificationTypeEnum::NEW_REVIEW,
            title: 'Saņēmi jaunu atsauksmi',
            body: 'Kāds atstāja ' . $rating . '★ atsauksmi par darbu "' . $jobRequest->getTitle() . '".',
            actionUrl: route('dashboard'),
            metadata: ['job_request_id' => $jobRequestId, 'review_id' => $review->getId()],
        ));

        return $review;
    }
}
