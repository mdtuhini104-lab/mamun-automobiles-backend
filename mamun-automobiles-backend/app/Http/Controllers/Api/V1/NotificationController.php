<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use App\Models\NotificationLog;
use App\Services\NotificationQueueService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $queueService;

    public function __construct(NotificationQueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    public function templates()
    {
        $templates = NotificationTemplate::all();
        return response()->json($templates);
    }

    public function logs(Request $request)
    {
        $query = NotificationLog::query();

        if ($request->has('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function send(Request $request)
    {
        $request->validate([
            'event_key' => 'required|string',
            'recipient' => 'required|string',
            'data' => 'nullable|array',
            'customer_id' => 'nullable|integer'
        ]);

        $this->queueService->enqueue(
            $request->event_key,
            $request->recipient,
            $request->data ?? [],
            $request->customer_id
        );

        return response()->json(['message' => 'Notification queued successfully'], 202);
    }

    /**
     * Get database notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $notifications = $user->notifications()->paginate(20);
        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead($id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
            'unread_count' => $user->unreadNotifications()->count()
        ]);
    }

    /**
     * Clear all read notifications.
     */
    public function clear()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user->notifications()->whereNotNull('read_at')->delete();

        return response()->json([
            'message' => 'Read notifications cleared successfully',
            'unread_count' => $user->unreadNotifications()->count()
        ]);
    }
}
