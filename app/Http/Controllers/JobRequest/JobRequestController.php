<?php

declare(strict_types=1);

namespace App\Http\Controllers\JobRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobRequest\SaveJobRequest;
use App\Models\JobRequest;
use App\Services\Repositories\JobRequest\JobRequestLogicRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class JobRequestController extends Controller
{
    public function __construct(
        private readonly JobRequestLogicRepository $logicRepository
    ) {
    }

    public function store(SaveJobRequest $request): RedirectResponse
    {
        $this->logicRepository->createJobRequest($request->toDTO());

        return redirect()->route('seeker.job-requests.index')->with('success', 'Darba sludinājums veiksmīgi izveidots!');
    }

    public function update(SaveJobRequest $request, JobRequest $jobRequest): RedirectResponse
    {
        $this->logicRepository->updateJobRequest(
            $jobRequest, 
            $request->toDTO(), 
            Auth::id()
        );

        return back()->with('success', 'Sludinājums veiksmīgi atjaunināts!');
    }

    public function destroy(JobRequest $jobRequest): RedirectResponse
    {
        $this->logicRepository->deleteJobRequest($jobRequest, Auth::id());

        return back()->with('info', 'Sludinājums ir izdzēsts.');
    }
}