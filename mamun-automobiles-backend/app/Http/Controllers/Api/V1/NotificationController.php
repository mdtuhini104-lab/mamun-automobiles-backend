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
}
