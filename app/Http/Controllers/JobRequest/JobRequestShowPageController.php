<?php

declare(strict_types=1);

namespace App\Http\Controllers\JobRequest;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobLifecycleResource;
use App\Repositories\JobRequestRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JobRequestShowPageController extends Controller
{
    public function __construct(
        private readonly JobRequestRepository $repository,
    ) {}

    public function show(Request $request, int $id): Response
    {
        $job = $this->repository->findById($id);
        abort_if(!$job, 404);
        abort_if($job->getUserId() !== $request->user()->getId(), 403);

        return Inertia::render('Seeker/JobRequests/Show', [
            'job' => (new JobLifecycleResource($job))->toArray($request),
        ]);
    }
}
