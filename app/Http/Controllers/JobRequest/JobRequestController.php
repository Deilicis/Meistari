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
    private const MSG_CREATED   = 'Darba sludinājums veiksmīgi izveidots!';
    private const MSG_UPDATED   = 'Sludinājums veiksmīgi atjaunināts!';
    private const MSG_DELETED   = 'Sludinājums ir izdzēsts.';
    private const FLASH_SUCCESS = 'success';
    private const FLASH_INFO    = 'info';

    public function __construct(
        private readonly JobRequestLogicRepository $logicRepository
    ) {
    }

    public function store(SaveJobRequest $request): RedirectResponse
    {
        $this->logicRepository->createJobRequest($request->toDTO());

        return redirect()->route('seeker.job-requests.index')->with(self::FLASH_SUCCESS, self::MSG_CREATED);
    }

    public function update(SaveJobRequest $request, JobRequest $jobRequest): RedirectResponse
    {
        $this->logicRepository->updateJobRequest(
            $jobRequest,
            $request->toDTO(),
            Auth::id()
        );

        return back()->with(self::FLASH_SUCCESS, self::MSG_UPDATED);
    }

    public function destroy(JobRequest $jobRequest): RedirectResponse
    {
        $this->logicRepository->deleteJobRequest($jobRequest, Auth::id());

        return back()->with(self::FLASH_INFO, self::MSG_DELETED);
    }
}
