<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $query = AuditLog::with('user:id,name');

        if ($action = $request->get('action')) {
            $query->where(AuditLog::ACTION, $action);
        }

        if ($userId = $request->get('user_id')) {
            $query->where(AuditLog::USER_ID, $userId);
        }

        if ($type = $request->get('type')) {
            $query->where(AuditLog::AUDITABLE_TYPE, 'like', "%{$type}%");
        }

        return Inertia::render('Admin/AuditLogs/Index', [
            'auditLogs' => $query->latest()->paginate(50)->withQueryString(),
            'filters'   => $request->only(['action', 'user_id', 'type']),
        ]);
    }
}
