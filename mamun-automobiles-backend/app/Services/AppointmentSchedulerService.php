<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\WorkshopBay;
use App\Models\Holiday;
use App\Models\Employee;
use App\Models\JobCard;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class AppointmentSchedulerService
{
    protected $availabilityService;

    public function __construct(WorkforceAvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    /**
     * Checks if a branch has capacity on a specific date.
     */
    public function checkBayCapacity(int $branchId, string $date): bool
    {
        $totalCapacity = WorkshopBay::where('branch_id', $branchId)
            ->where('status', 'active')
            ->sum('max_vehicle_capacity');

        if ($totalCapacity <= 0) {
            // Default fallback capacity if no active bays are defined
            $totalCapacity = 5;
        }

        $appointmentCount = Appointment::where('branch_id', $branchId)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        return $appointmentCount < $totalCapacity;
    }

    /**
     * Checks if the date is a holiday or a weekly closed day (Friday).
     */
    public function isHoliday(string $date): bool
    {
        $carbonDate = Carbon::parse($date);
        
        // Mamun Automobiles weekly holiday is Friday
        if ($carbonDate->isFriday()) {
            return true;
        }

        return Holiday::whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();
    }

    /**
     * Prevents double booking for a vehicle or customer.
     */
    public function checkDoubleBooking(int $customerId, ?int $vehicleId, string $date): void
    {
        $query = Appointment::whereDate('appointment_date', $date)
            ->whereIn('status', ['pending', 'confirmed']);

        $query->where(function ($q) use ($customerId, $vehicleId) {
            $q->where('customer_id', $customerId);
            if ($vehicleId) {
                $q->orWhere('vehicle_id', $vehicleId);
            }
        });

        if ($query->exists()) {
            throw new Exception("Conflict Detected: Customer or Vehicle already has an active appointment on {$date}.");
        }
    }

    /**
     * AI-based estimated service duration.
     * Analyzes historical completed job cards of the same service type or returns smart defaults.
     */
    public function estimateServiceDuration(string $serviceType, ?int $vehicleId = null): array
    {
        // 1. Fetch completed job cards with similar service type
        $history = JobCard::whereNotNull('start_date')
            ->whereNotNull('delivery_date')
            ->where('service_status', 'completed')
            ->where('complaint', 'like', '%' . $serviceType . '%')
            ->take(15)
            ->get();

        $durations = [];
        foreach ($history as $jc) {
            $start = Carbon::parse($jc->start_date);
            $end = Carbon::parse($jc->delivery_date);
            $diff = $start->diffInMinutes($end);
            if ($diff > 5 && $diff < 2880) { // Limit to sensible durations (5m to 48h)
                $durations[] = $diff;
            }
        }

        $defaultDurations = [
            'tune-up' => 60,
            'ac service' => 120,
            'engine' => 360,
            'brake' => 90,
            'suspension' => 180,
            'wash' => 45,
            'dent' => 240,
            'paint' => 480,
        ];

        $matchedDefault = 90; // Default fallback
        foreach ($defaultDurations as $key => $min) {
            if (stripos($serviceType, $key) !== false) {
                $matchedDefault = $min;
                break;
            }
        }

        if (count($durations) >= 3) {
            $avgDuration = (int) (array_sum($durations) / count($durations));
            $confidence = count($durations) >= 8 ? 0.95 : 0.80;
            $explanation = "Estimated based on " . count($durations) . " historical matching completed job cards.";
        } else {
            $avgDuration = $matchedDefault;
            $confidence = 0.50;
            $explanation = "Default estimate applied due to insufficient historical dataset for this service type.";
        }

        // Adjust for older vehicles if details are available
        if ($vehicleId) {
            $vehicle = \App\Models\Vehicle::find($vehicleId);
            if ($vehicle && $vehicle->manufacture_year && (date('Y') - $vehicle->manufacture_year) > 10) {
                // Add 15% complexity duration buffer for vehicles older than 10 years
                $avgDuration = (int) ($avgDuration * 1.15);
                $explanation .= " Recalculated with +15% complexity adjustment for aging vehicle model ({$vehicle->manufacture_year}).";
            }
        }

        return [
            'duration_minutes' => $avgDuration,
            'confidence' => $confidence,
            'explanation' => $explanation,
        ];
    }

    /**
     * Balances technician workload and suggests the most suitable active mechanic.
     */
    public function findAvailableTechnician(int $branchId, string $date): ?Employee
    {
        // 1. Get all employees in the branch with role/type matching mechanic
        $mechanics = Employee::where('branch_id', $branchId)
            ->where('status', 'active')
            ->get();

        $selectedMechanic = null;
        $minLoad = 999;

        foreach ($mechanics as $mechanic) {
            // Check availability via standard workforce schedule logic
            if (!$this->availabilityService->isAvailable($mechanic, $date)) {
                continue;
            }

            // Count scheduled appointments for this mechanic on the target date
            $workload = Appointment::where('mechanic_id', $mechanic->user_id)
                ->whereDate('appointment_date', $date)
                ->whereIn('status', ['pending', 'confirmed'])
                ->count();

            if ($workload < $minLoad) {
                $minLoad = $workload;
                $selectedMechanic = $mechanic;
            }
        }

        return $selectedMechanic;
    }

    /**
     * Books a capacity-aware appointment.
     */
    public function reserveSlot(array $data): Appointment
    {
        $branchId = (int) ($data['branch_id'] ?? (auth()->check() && auth()->user()->branch_id ? auth()->user()->branch_id : 1));
        $tenantId = (int) ($data['tenant_id'] ?? (auth()->check() && auth()->user()->tenant_id ? auth()->user()->tenant_id : 1));
        $date = $data['appointment_date'];

        // 1. Holiday constraint validation
        if ($this->isHoliday($date)) {
            throw new Exception("Cannot book appointment: Selected date falls on a weekend or registered holiday.");
        }

        // 2. Capacity constraint validation
        if (!$this->checkBayCapacity($branchId, $date)) {
            throw new Exception("Workshop capacity overload: No bays available on {$date}.");
        }

        // 3. Double-booking checks
        $this->checkDoubleBooking($data['customer_id'], $data['vehicle_id'] ?? null, $date);

        // 4. Estimate duration
        $aiDuration = $this->estimateServiceDuration($data['service_type'], $data['vehicle_id'] ?? null);

        // 5. Try workload balancing to find a mechanic
        $suggestedMechanic = $this->findAvailableTechnician($branchId, $date);

        return DB::transaction(function () use ($data, $tenantId, $branchId, $aiDuration, $suggestedMechanic) {
            return Appointment::create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'customer_id' => $data['customer_id'],
                'vehicle_id' => $data['vehicle_id'] ?? null,
                'mechanic_id' => $suggestedMechanic ? $suggestedMechanic->user_id : ($data['mechanic_id'] ?? null),
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'] ?? '09:00:00',
                'service_type' => $data['service_type'],
                'status' => 'pending',
                'remarks' => $data['remarks'] ?? $aiDuration['explanation']
            ]);
        });
    }
}
