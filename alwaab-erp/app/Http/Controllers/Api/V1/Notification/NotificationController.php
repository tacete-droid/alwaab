<?php

namespace App\Http\Controllers\Api\V1\Notification;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\AppNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private AppNotificationService $notifications) {}

    public function poll(Request $request): JsonResponse
    {
        return ApiResponse::success(
            $this->notifications->poll($request->user(), $request->query('since'))
        );
    }

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(
            $this->notifications->list($request->user(), $request->integer('per_page', 30))
        );
    }

    public function markRead(Request $request, string $notification): JsonResponse
    {
        $this->notifications->markRead($request->user(), $notification);

        return ApiResponse::success(message: __('messages.updated'));
    }
}
