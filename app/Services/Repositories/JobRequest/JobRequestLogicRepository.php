<?php

declare(strict_types=1);

namespace App\Services\Repositories\JobRequest;

use App\Constants\ErrorMessages;
use App\DataTransferObjects\JobRequest\SaveJobRequestData;
use App\DTOs\Notifications\CreateNotificationDTO;
use App\Enums\Job\JobStatusEnum;
use App\Enums\NotificationTypeEnum;
use App\Models\JobRequest;
use App\Notifications\Job\JobCompletedNotification;
use App\Repositories\CategoryRepository;
use App\Services\NotificationService;
use App\Services\Repositories\Application\ApplicationDbRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JobRequestLogicRepository
{
    public function __construct(
        private readonly JobRequestDbRepository  $dbRepository,
        private readonly ApplicationDbRepository $applicationDbRepository,
        private readonly NotificationService     $notificationService,
        private readonly CategoryRepository      $categoryRepository,
    ) {
    }

    public function getPaginatedJobRequests(): LengthAwarePaginator
    {
        return $this->dbRepository->getPaginated();
    }

    public function getPublicJobRequests(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return $this->dbRepository->getPublicPaginated($filters, $perPage);
    }

    public function getUserJobRequests(int $userId, array $filters = []): Collection
    {
        return $this->dbRepository->getByUserId($userId, $filters);
    }

    public function getJobRequestById(int $id): JobRequest
    {
        return $this->dbRepository->getById($id);
    }

    public function createJobRequest(SaveJobRequestData $dto): JobRequest
    {
        $images = [];

        if (!empty($dto->images)) {
            foreach ($dto->images as $image) {
                if ($image instanceof UploadedFile) {
                    $images[] = $image->store(JobRequest::IMAGES_DIR, JobRequest::STORAGE_DISK);
                }
            }
        }

        $slug = Str::slug($dto->title) . '-' . uniqid();

        if ($dto->pendingCategorySuggestionId !== null) {
            $cits = $this->categoryRepository->findBySlug('cits');
            if ($cits) {
                $dto->categoryId = $cits->getId();
            }
        }

        $dataToSave = [
            JobRequest::USER_ID => $dto->userId,
            JobRequest::CATEGORY_ID => $dto->categoryId,
            JobRequest::SLUG => $slug,
            JobRequest::TITLE => $dto->title,
            JobRequest::DESCRIPTION => $dto->description,
            JobRequest::STATUS => $dto->status->value,
            JobRequest::BUDGET => $dto->budget,
            JobRequest::DEADLINE => $dto->deadline,
            JobRequest::LOCATION => $dto->location,
            JobRequest::IMAGES => $images,
        ];

        if ($dto->pendingCategorySuggestionId !== null) {
            $dataToSave[JobRequest::PENDING_CATEGORY_SUGGESTION_ID] = $dto->pendingCategorySuggestionId;
        }

        return $this->dbRepository->create($dataToSave);
    }

    public function updateJobRequest(JobRequest $jobRequest, SaveJobRequestData $dto, int $currentUserId): bool
    {
        if ($jobRequest->getUserId() !== $currentUserId) {
            abort(403, ErrorMessages::JOB_REQUEST_EDIT_FORBIDDEN);
        }

        $images = $jobRequest->getImages();

        if (!empty($dto->imagesToDelete)) {
            foreach ($dto->imagesToDelete as $path) {
                Storage::disk(JobRequest::STORAGE_DISK)->delete($path);
                $images = array_filter($images, fn($p) => $p !== $path);
            }
            $images = array_values($images);
        }

        if (!empty($dto->images)) {
            foreach ($dto->images as $image) {
                if ($image instanceof UploadedFile) {
                    $images[] = $image->store(JobRequest::IMAGES_DIR, JobRequest::STORAGE_DISK);
                }
            }
        }

        $dataToUpdate = [
            JobRequest::CATEGORY_ID => $dto->categoryId,
            JobRequest::TITLE => $dto->title,
            JobRequest::DESCRIPTION => $dto->description,
            JobRequest::BUDGET => $dto->budget,
            JobRequest::DEADLINE => $dto->deadline,
            JobRequest::LOCATION => $dto->location,
            JobRequest::IMAGES => $images,
        ];

        return $this->dbRepository->update($jobRequest, $dataToUpdate);
    }

    public function deleteJobRequest(JobRequest $jobRequest, int $currentUserId): ?bool
    {
        if ($jobRequest->getUserId() !== $currentUserId) {
            abort(403, ErrorMessages::JOB_REQUEST_DELETE_FORBIDDEN);
        }

        return $this->dbRepository->delete($jobRequest);
    }

    public function completeJob(int $jobRequestId, int $seekerId): JobRequest
    {
        $jobRequest = JobRequest::findOrFail($jobRequestId);

        if ($jobRequest->getUserId() !== $seekerId) {
            abort(403, ErrorMessages::JOB_NOT_YOURS);
        }

        if ($jobRequest->getStatus() !== JobStatusEnum::ACCEPTED) {
            abort(422, ErrorMessages::JOB_NOT_ASSIGNED);
        }

        $completed = $this->dbRepository->setCompleted($jobRequest);

        $acceptedApplication = $this->applicationDbRepository->findAcceptedForJob($jobRequestId);
        if ($acceptedApplication) {
            $this->applicationDbRepository->setCompleted($acceptedApplication);

            $acceptedApplication->load('user');
            $acceptedApplication->user->notify(new JobCompletedNotification($completed));

            $this->notificationService->create(new CreateNotificationDTO(
                userId: $acceptedApplication->getUserId(),
                type: NotificationTypeEnum::JOB_COMPLETED,
                title: 'Darbs atzīmēts kā pabeigts',
                body: '"' . $completed->getTitle() . '" ir atzīmēts kā pabeigts.',
                actionUrl: route('jobs.show', $jobRequestId),
                metadata: ['job_request_id' => $jobRequestId],
            ));
        }

        $this->notificationService->create(new CreateNotificationDTO(
            userId: $seekerId,
            type: NotificationTypeEnum::JOB_COMPLETED,
            title: 'Darbs pabeigts',
            body: '"' . $completed->getTitle() . '" ir pabeigts. Vari atstāt atsauksmi.',
            actionUrl: route('jobs.show', $jobRequestId),
            metadata: ['job_request_id' => $jobRequestId],
        ));

        return $completed;
    }
}
