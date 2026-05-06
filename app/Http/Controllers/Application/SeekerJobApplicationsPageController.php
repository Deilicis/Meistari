<?php

declare(strict_types=1);

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Http\Resources\SeekerJobApplicationResource;
use App\Services\Repositories\Application\ApplicationLogicRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SeekerJobApplicationsPageController extends Controller
{
    public function __construct(
        private readonly ApplicationLogicRepository $logicRepository
    ) {}

    public function index(Request $request): Response
    {
        $applications = $this->logicRepository->getSeekerReceivedApplications($request->user()->getId());

        return Inertia::render('Seeker/JobApplications/Index', [
            'applications' => SeekerJobApplicationResource::collection($applications),
        ]);
    }
}
