<?php

declare(strict_types=1);

namespace App\Services\Repositories\JobRequest;

use App\Constants\ErrorMessages;
use App\DataTransferObjects\JobRequest\SaveJobRequestData;
use App\Models\JobRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JobRequestLogicRepository
{
    public function __construct(
        private readonly JobRequestDbRepository $dbRepository
    ) {
    }

    public function getPaginatedJobRequests(): LengthAwarePaginator
    {
        return $this->dbRepository->getPaginated();
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

        $dataToSave = [
            JobRequest::USER_ID     => $dto->userId,
            JobRequest::CATEGORY_ID => $dto->categoryId,
            JobRequest::SLUG        => $slug,
            JobRequest::TITLE       => $dto->title,
            JobRequest::DESCRIPTION => $dto->description,
            JobRequest::STATUS      => $dto->status->value,
            JobRequest::BUDGET      => $dto->budget,
            JobRequest::DEADLINE    => $dto->deadline,
            JobRequest::LOCATION    => $dto->location,
            JobRequest::IMAGES      => $images,
        ];

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
            JobRequest::TITLE       => $dto->title,
            JobRequest::DESCRIPTION => $dto->description,
            JobRequest::BUDGET      => $dto->budget,
            JobRequest::DEADLINE    => $dto->deadline,
            JobRequest::LOCATION    => $dto->location,
            JobRequest::IMAGES      => $images,
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
}
