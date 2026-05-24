<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Customer;
use App\Models\Appointment;
use App\Models\CustomerActivity;
use App\Services\NotificationService;
use App\Traits\ApiResponseTrait;

class CrmController extends Controller
{
    use ApiResponseTrait;

    public function getCustomerTimeline($id): JsonResponse
    {
        $customer = Customer::with(['activities', 'jobCards.items', 'invoices', 'appointments', 'vehicles'])->findOrFail($id);
        return $this->successResponse($customer, 'Customer timeline fetched');
    }

    public function updateCustomerTags(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'tag' => 'required|string',
            'notes' => 'nullable|string'
        ]);
        
        $customer = Customer::findOrFail($id);
        $customer->update($validated);
        
        return $this->successResponse($customer, 'Customer tags updated');
    }

    public function getAppointments(): JsonResponse
    {
        $appointments = Appointment::with(['customer', 'vehicle', 'mechanic'])->orderBy('appointment_date', 'asc')->get();
        return $this->successResponse($appointments, 'Appointments loaded');
    }

    public function storeAppointment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'mechanic_id' => 'nullable|exists:users,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'service_type' => 'required|string',
            'remarks' => 'nullable|string'
        ]);

        $appointment = Appointment::create($validated);
        
        CustomerActivity::create([
            'customer_id' => $appointment->customer_id,
            'type' => 'appointment_booked',
            'description' => "Appointment booked for " . $appointment->appointment_date . " at " . $appointment->appointment_time
        ]);

        $customer = Customer::find($appointment->customer_id);
        NotificationService::sendSMS($customer->phone, "Your service appointment is confirmed for " . $appointment->appointment_date);

        return $this->successResponse($appointment, 'Appointment booked successfully');
    }

    public function updateAppointmentStatus(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled'
        ]);
        
        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => $validated['status']]);
        
        if ($validated['status'] === 'completed') {
            $customer = Customer::find($appointment->customer_id);
            $customer->increment('loyalty_points', 50);
            
            CustomerActivity::create([
                'customer_id' => $appointment->customer_id,
                'type' => 'service_completed',
                'description' => "Service appointment completed on " . date('Y-m-d') . ". 50 Loyalty points added."
            ]);
            
            NotificationService::sendWhatsApp($customer->phone, "Your vehicle service is completed. Thank you for choosing Mamun Automobiles!");
        }

        return $this->successResponse($appointment, 'Appointment status updated');
    }
}

