<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->getId();

        return response()->json([
            'notifications' => NotificationResource::collection(
                $this->notificationService->getRecentForUser($userId)
            ),
            'unread_count' => $this->notificationService->getUnreadCountForUser($userId),
        ]);
    }

    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $this->notificationService->markAsRead($id, $request->user()->getId());

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $this->notificationService->markAllAsRead($request->user()->getId());

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->notificationService->delete($id, $request->user()->getId());

        return response()->json(['success' => true]);
    }
}
