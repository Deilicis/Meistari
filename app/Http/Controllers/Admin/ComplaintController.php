<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\Complaint\ComplaintStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateComplaintRequest;
use App\Models\Complaint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ComplaintController extends Controller
{
    private const MSG_UPDATED   = 'Sūdzība atjaunināta.';
    private const FLASH_SUCCESS = 'success';

    public function index(Request $request): Response
    {
        $query = Complaint::with(['reporter:id,name', 'reportedUser:id,name', 'resolvedBy:id,name']);

        if ($status = $request->get('status')) {
            $query->where(Complaint::STATUS, $status);
        }

        return Inertia::render('Admin/Complaints/Index', [
            'complaints' => $query->latest()->paginate(20)->withQueryString(),
            'filters'    => $request->only(['status']),
            'statuses'   => ComplaintStatusEnum::cases(),
        ]);
    }

    public function show(Complaint $complaint): Response
    {
        $complaint->load(['reporter.profile', 'reportedUser.profile', 'resolvedBy.profile', 'reportedEntity']);

        return Inertia::render('Admin/Complaints/Show', [
            'complaint' => $complaint,
        ]);
    }

    public function update(UpdateComplaintRequest $request, Complaint $complaint): RedirectResponse
    {
        $status = ComplaintStatusEnum::from($request->validated(Complaint::STATUS));

        $data = [
            Complaint::STATUS          => $status->value,
            Complaint::RESOLUTION_NOTE => $request->validated(Complaint::RESOLUTION_NOTE),
        ];

        if (in_array($status, [ComplaintStatusEnum::RESOLVED, ComplaintStatusEnum::DISMISSED], true)) {
            $data[Complaint::RESOLVED_BY] = auth()->id();
            $data[Complaint::RESOLVED_AT] = now();
        }

        $complaint->update($data);

        return back()->with(self::FLASH_SUCCESS, self::MSG_UPDATED);
    }
}
