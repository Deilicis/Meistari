<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminJobRequestController extends Controller
{
    private const MSG_UPDATED = 'Sludinājums atjaunināts.';
    private const MSG_DELETED = 'Sludinājums dzēsts.';
    private const FLASH_SUCCESS = 'success';

    public function index(Request $request): Response
    {
        $query = JobRequest::with(['user.profile', 'category']);

        if ($search = $request->get('search')) {
            $query->where(JobRequest::TITLE, 'like', "%{$search}%");
        }

        if ($status = $request->get('status')) {
            $query->where(JobRequest::STATUS, $status);
        }

        if ($categoryId = $request->get('category_id')) {
            $query->where(JobRequest::CATEGORY_ID, $categoryId);
        }

        return Inertia::render('Admin/JobRequests/Index', [
            'jobRequests' => $query->latest()->paginate(20)->withQueryString(),
            'filters'     => $request->only(['search', 'status', 'category_id']),
        ]);
    }

    public function show(JobRequest $jobRequest): Response
    {
        $jobRequest->load(['user.profile', 'category', 'applications.user.profile']);

        return Inertia::render('Admin/JobRequests/Show', [
            'jobRequest' => $jobRequest,
        ]);
    }

    public function update(Request $request, JobRequest $jobRequest): RedirectResponse
    {
        $jobRequest->update($request->only([
            JobRequest::TITLE,
            JobRequest::DESCRIPTION,
            JobRequest::STATUS,
            JobRequest::BUDGET,
            JobRequest::DEADLINE,
            JobRequest::LOCATION,
        ]));

        return back()->with(self::FLASH_SUCCESS, self::MSG_UPDATED);
    }

    public function destroy(JobRequest $jobRequest): RedirectResponse
    {
        $jobRequest->delete();

        return redirect()->route('admin.job-requests.index')
            ->with(self::FLASH_SUCCESS, self::MSG_DELETED);
    }
}
