<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\QualityControl;
use App\Models\VehicleDelivery;
use App\Models\WorkOrder;
use App\Models\JobCard;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QcAndDeliveryController extends Controller
{
    use ApiResponseTrait;

    /**
     * Submit a Quality Control (QC) check for a Work Order.
     */
    public function submitQc(Request $request): JsonResponse
    {
        $request->validate([
            'work_order_id' => 'required|exists:work_orders,id',
            'status' => 'required|string|in:passed,failed',
            'checklist' => 'required|array',
            'road_test_performed' => 'required|boolean',
            'road_test_notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $workOrder = WorkOrder::findOrFail($request->work_order_id);

            // Create Quality Control entry
            $qc = QualityControl::create([
                'work_order_id' => $request->work_order_id,
                'status' => $request->status,
                'supervisor_id' => auth()->id() ?? 1,
                'checklist' => $request->checklist,
                'road_test_performed' => $request->road_test_performed,
                'road_test_notes' => $request->road_test_notes,
                'inspected_at' => now(),
            ]);

            // Sync with Work Order and Job Card Workflow states
            $jobCard = $workOrder->jobCard;
            if ($request->status === 'passed') {
                $workOrder->status = 'completed';
                $workOrder->save();

                // Transition state to 'ready_for_delivery'
                $jobCard->service_status = \App\Enums\ServiceStatus::COMPLETED;
                $jobCard->save();

                \App\Models\WorkflowHistory::create([
                    'job_card_id' => $jobCard->id,
                    'from_state' => 'work_order_active',
                    'to_state' => 'ready_for_delivery',
                    'user_id' => auth()->id() ?? 1,
                    'notes' => 'Quality control inspection passed. Ready for delivery.',
                ]);
            } else {
                \App\Models\WorkflowHistory::create([
                    'job_card_id' => $jobCard->id,
                    'from_state' => 'work_order_active',
                    'to_state' => 'qc_failed',
                    'user_id' => auth()->id() ?? 1,
                    'notes' => 'QC check failed. Work order reverted for adjustments.',
                ]);
            }

            return $this->successResponse($qc, 'Quality Control report logged successfully', 201);
        });
    }

    /**
     * Submit final vehicle delivery handover confirmation.
     */
    public function submitDelivery(Request $request): JsonResponse
    {
        $request->validate([
            'job_card_id' => 'required|exists:job_cards,id',
            'delivered_to' => 'required|string|min:3|max:100',
            'signature_path' => 'nullable|string',
            'delivery_photos' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $jobCard = JobCard::findOrFail($request->job_card_id);

            // Log final vehicle delivery handover
            $delivery = VehicleDelivery::create([
                'job_card_id' => $request->job_card_id,
                'delivered_to' => $request->delivered_to,
                'signature_path' => $request->signature_path,
                'delivery_photos' => $request->delivery_photos,
                'delivered_by_id' => auth()->id() ?? 1,
                'delivered_at' => now(),
                'notes' => $request->notes,
            ]);

            // Dispatch VehicleDelivered event
            event(new \App\Events\VehicleDelivered($delivery));

            // Transition JobCard status to 'closed'
            $jobCard->service_status = \App\Enums\ServiceStatus::COMPLETED;
            $jobCard->save();

            \App\Models\WorkflowHistory::create([
                'job_card_id' => $jobCard->id,
                'from_state' => 'ready_for_delivery',
                'to_state' => 'closed',
                'user_id' => auth()->id() ?? 1,
                'notes' => "Vehicle handed over to {$request->delivered_to}. Job card closed.",
            ]);

            return $this->successResponse($delivery, 'Vehicle delivery handover logged successfully. Job Card closed.', 201);
        });
    }
}
