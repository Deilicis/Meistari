<?php

declare(strict_types=1);

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationListResource;
use App\Services\Repositories\Application\ApplicationLogicRepository;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationPageController extends Controller
{
    public function __construct(
        private readonly ApplicationLogicRepository $logicRepository
    ) {}

    public function index(): Response
    {
        $applications = $this->logicRepository->getMasterApplications(Auth::id());

        return Inertia::render('Master/Applications/MyApplications', [
            'applications' => $applications->map(fn($app) => (new ApplicationListResource($app))->resolve()),
        ]);
    }
}
