<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Warranty;
use App\Models\ComebackJob;
use App\Models\JobCard;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarrantyAndComebackController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get list of all warranties.
     */
    public function warranties(Request $request): JsonResponse
    {
        $query = Warranty::with(['jobCard.customer', 'jobCard.vehicle', 'invoice']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return $this->successResponse($query->latest()->paginate(20), 'Warranties retrieved successfully');
    }

    /**
     * Get list of all comeback jobs.
     */
    public function comebacks(Request $request): JsonResponse
    {
        $query = ComebackJob::with([
            'originalJobCard.customer',
            'originalJobCard.vehicle',
            'comebackJobCard',
            'technicianAtFault'
        ]);

        if ($request->has('technician_id')) {
            $query->where('technician_at_fault_id', $request->technician_id);
        }

        return $this->successResponse($query->latest()->paginate(20), 'Comeback jobs retrieved successfully');
    }

    /**
     * Store a new Comeback Job log.
     */
    public function storeComeback(Request $request): JsonResponse
    {
        $request->validate([
            'original_job_card_id' => 'required|exists:job_cards,id',
            'comeback_job_card_id' => 'required|exists:job_cards,id',
            'reason' => 'required|string|min:10',
            'technician_at_fault_id' => 'nullable|exists:users,id',
        ]);

        return DB::transaction(function () use ($request) {
            $originalJobCard = JobCard::findOrFail($request->original_job_card_id);
            $comebackJobCard = JobCard::findOrFail($request->comeback_job_card_id);

            // Update the warranty of the original job card if active to 'claimed'
            $warranty = Warranty::where('job_card_id', $originalJobCard->id)
                ->where('status', 'active')
                ->first();

            if ($warranty) {
                $warranty->update(['status' => 'claimed']);
            }

            // Create the comeback job entry
            $comeback = ComebackJob::create([
                'original_job_card_id' => $request->original_job_card_id,
                'comeback_job_card_id' => $request->comeback_job_card_id,
                'reason' => $request->reason,
                'technician_at_fault_id' => $request->technician_at_fault_id,
            ]);

            // Create a System Alert or Notification for supervisors/managers
            $technicianName = $comeback->technicianAtFault->name ?? 'Unknown';
            $title = 'Comeback Job Registered';
            $regNo = $originalJobCard->vehicle->registration_no ?? 'Unknown';
            $message = "Vehicle {$regNo} returned for repeat repairs (Faulty Technician: {$technicianName}).";
            
            $admins = \App\Models\User::whereHas('roles', function($q) {
                $q->whereIn('name', ['Super Admin', 'Admin', 'Manager', 'Supervisor']);
            })->get();

            \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SystemNotification(
                $title,
                $message,
                'comeback_job_alert',
                [
                    'comeback_id' => $comeback->id,
                    'original_job_card_id' => $originalJobCard->id,
                    'comeback_job_card_id' => $comebackJobCard->id
                ]
            ));

            return $this->successResponse($comeback, 'Comeback job logged successfully', 201);
        });
    }
}
