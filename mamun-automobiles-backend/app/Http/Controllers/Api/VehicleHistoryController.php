<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('vehicle_service_histories')
            ->join('vehicles', 'vehicle_service_histories.vehicle_id', '=', 'vehicles.id')
            ->join('customers', 'vehicle_service_histories.customer_id', '=', 'customers.id')
            ->select('vehicle_service_histories.*', 'vehicles.registration_number', 'vehicles.make', 'vehicles.model', 'customers.name as customer_name');
            
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('vehicles.registration_number', 'like', "%{$search}%")
                  ->orWhere('customers.name', 'like', "%{$search}%");
        }
        
        if ($request->has('vehicle_id')) {
            $query->where('vehicle_service_histories.vehicle_id', $request->vehicle_id);
        }

        return response()->json($query->orderBy('service_date', 'desc')->paginate(15));
    }

    public function show($vehicle)
    {
        $history = DB::table('vehicle_service_histories')
            ->where('vehicle_id', $vehicle)
            ->orderBy('service_date', 'desc')
            ->get();
            
        $vehicleData = DB::table('vehicles')->where('id', $vehicle)->first();
        
        if (!$vehicleData) {
            return response()->json(['message' => 'Vehicle not found'], 404);
        }
        
        return response()->json(['vehicle' => $vehicleData, 'history' => $history]);
    }
    
    public function timeline($vehicle)
    {
        $timeline = DB::table('vehicle_service_histories')
            ->where('vehicle_id', $vehicle)
            ->orderBy('service_date', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'date' => $item->service_date,
                    'title' => 'Service at ' . $item->mileage . ' km',
                    'mechanic' => $item->mechanic_name,
                    'complaints' => explode(',', $item->complaints),
                    'services' => explode(',', $item->services_done),
                    'parts' => explode(',', $item->parts_changed),
                    'cost' => $item->total_cost,
                    'next_service' => $item->next_service_date
                ];
            });
            
        return response()->json($timeline);
    }
}
