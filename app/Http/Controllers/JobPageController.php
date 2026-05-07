<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Jobs\JobPageService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JobPageController extends Controller
{
    public function __construct(
        private readonly JobPageService $pageService,
    ) {}

    public function show(Request $request, int $jobId): Response
    {
        $payload = $this->pageService->buildPayloadForUser($jobId, $request->user());

        return Inertia::render('Jobs/Show', $payload);
    }
}
