<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminServiceController extends Controller
{
    private const MSG_UPDATED = 'Pakalpojums atjaunināts.';
    private const MSG_DELETED = 'Pakalpojums dzēsts.';
    private const FLASH_SUCCESS = 'success';

    public function index(Request $request): Response
    {
        $query = Service::with(['user.profile', 'category']);

        if ($search = $request->get('search')) {
            $query->where(Service::TITLE, 'like', "%{$search}%");
        }

        if ($categoryId = $request->get('category_id')) {
            $query->where(Service::CATEGORY_ID, $categoryId);
        }

        if ($request->has('is_active')) {
            $query->where(Service::IS_ACTIVE, (bool) $request->get('is_active'));
        }

        return Inertia::render('Admin/Services/Index', [
            'services' => $query->latest()->paginate(20)->withQueryString(),
            'filters'  => $request->only(['search', 'category_id', 'is_active']),
        ]);
    }

    public function show(Service $service): Response
    {
        $service->load(['user.profile', 'category']);

        return Inertia::render('Admin/Services/Show', [
            'service' => $service,
        ]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $service->update($request->only([
            Service::TITLE,
            Service::DESCRIPTION,
            Service::PRICE,
            Service::IS_ACTIVE,
            Service::LOCATION,
        ]));

        return back()->with(self::FLASH_SUCCESS, self::MSG_UPDATED);
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()->route('admin.services.index')
            ->with(self::FLASH_SUCCESS, self::MSG_DELETED);
    }
}
