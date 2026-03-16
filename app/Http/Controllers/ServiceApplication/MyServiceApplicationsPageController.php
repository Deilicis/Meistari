<?php

declare(strict_types=1);

namespace App\Http\Controllers\ServiceApplication;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceApplicationListResource;
use App\Services\Repositories\ServiceApplication\ServiceApplicationLogicRepository;
use Inertia\Inertia;
use Inertia\Response;

class MyServiceApplicationsPageController extends Controller
{
    public function __construct(
        private readonly ServiceApplicationLogicRepository $applicationRepository
    ) {
    }

    public function index(): Response
    {
        $applications = $this->applicationRepository->getUserApplications(auth()->id());

        return Inertia::render('Seeker/ServiceApplications/MyServiceApplications', [
            'applications' => $applications->map(fn ($app) => (new ServiceApplicationListResource($app))->resolve()),
        ]);
    }
}
