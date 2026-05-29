<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\WorkOrderService;
use App\Http\Requests\AddAdditionalConsumptionRequest;
use App\Http\Resources\WorkOrderResource;
use App\Models\WorkOrder;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkOrderController extends Controller
{
    use ApiResponseTrait;

    protected $workOrderService;

    public function __construct(WorkOrderService $workOrderService)
    {
        $this->workOrderService = $workOrderService;
    }

    /**
     * List all work orders.
     */
    public function index(Request $request): JsonResponse
    {
        $query = WorkOrder::with(['jobCard.customer', 'quotation']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('job_card_id')) {
            $query->where('job_card_id', $request->job_card_id);
        }

        $workOrders = $query->orderBy('created_at', 'desc')->paginate(15);
        return $this->successResponse(
            WorkOrderResource::collection($workOrders->items()),
            'Work Orders retrieved successfully',
            200,
            [
                'current_page' => $workOrders->currentPage(),
                'per_page' => $workOrders->perPage(),
                'total' => $workOrders->total(),
                'last_page' => $workOrders->lastPage(),
            ]
        );
    }

    /**
     * Get details of a work order.
     */
    public function show(int $id): JsonResponse
    {
        $workOrder = WorkOrder::with([
            'jobCard.customer',
            'jobCard.vehicle',
            'jobCard.tasks.taskAssignments.employee',
            'jobCard.assignments.employee',
            'quotation.items.part',
            'consumptions.part',
            'consumptions.consumedBy'
        ])->find($id);

        if (!$workOrder) {
            return $this->errorResponse('Work Order not found', 404);
        }

        return $this->successResponse(
            new WorkOrderResource($workOrder),
            'Work Order details retrieved successfully'
        );
    }

    /**
     * Update status of a work order.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:pending,in_progress,paused,completed,cancelled',
        ]);

        try {
            $this->workOrderService->updateStatus($id, $request->status);
            return $this->successResponse(null, 'Work Order status updated to ' . $request->status . ' successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Log additional product or service consumption mid-repair.
     */
    public function addConsumption(AddAdditionalConsumptionRequest $request, int $id): JsonResponse
    {
        try {
            $this->workOrderService->addAdditionalItem($id, $request->validated());
            return $this->successResponse(null, 'Additional consumption logged and synchronized successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Get Live Workshop Operations Board details.
     */
    public function liveBoard(Request $request): JsonResponse
    {
        $bays = \App\Models\WorkshopBay::with(['currentJobCard.vehicle', 'currentJobCard.assignments.employee'])->get();
        
        $activeWorkOrders = WorkOrder::with(['jobCard.vehicle', 'jobCard.tasks.taskAssignments.employee'])
            ->whereIn('status', ['pending', 'in_progress', 'paused'])
            ->get();

        // Enforce Spatie cross-guard queries safely
        $technicians = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('name', 'Technician');
        })->with(['assignedJobs' => function ($q) {
            $q->whereNotIn('service_status', ['completed', 'cancelled']);
        }])->get();

        $delayedTasks = [];
        $overdueCount = 0;
        
        foreach ($activeWorkOrders as $wo) {
            $jobCard = $wo->jobCard;
            if (!$jobCard) continue;
            
            foreach ($jobCard->tasks as $task) {
                if ($task->status === 'in_progress' && $task->actual_minutes > $task->estimated_minutes) {
                    $overdueCount++;
                    $delayedTasks[] = [
                        'task_id' => $task->id,
                        'task_name' => $task->name,
                        'vehicle' => $jobCard->vehicle->plate_number ?? 'Unknown',
                        'estimated' => $task->estimated_minutes,
                        'actual' => $task->actual_minutes,
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'bays' => $bays,
            'active_work_orders' => $activeWorkOrders,
            'technicians' => $technicians,
            'overdue_count' => $overdueCount,
            'delayed_tasks' => $delayedTasks,
        ]);
    }
}
