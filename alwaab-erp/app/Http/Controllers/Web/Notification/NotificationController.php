<?php

namespace App\Http\Controllers\Web\Notification;

use App\Http\Controllers\Controller;
use App\Services\AppNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function __construct(private AppNotificationService $notifications) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Notifications/Index', [
            'notifications' => $this->notifications->list($request->user()),
            'unread_count' => $this->notifications->unreadCount($request->user()),
        ]);
    }

    public function poll(Request $request): JsonResponse
    {
        return response()->json(
            $this->notifications->poll($request->user(), $request->query('since'))
        );
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $this->notifications->markRead($request->user(), $notification);

        return redirect()->back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $this->notifications->markAllRead($request->user());

        return redirect()->back()->with('success', __('notifications.all_read'));
    }
}
