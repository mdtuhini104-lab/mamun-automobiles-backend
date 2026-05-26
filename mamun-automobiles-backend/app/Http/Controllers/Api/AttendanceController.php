<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AttendanceService;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function checkIn(Request $request)
    {
        $deviceInfo = $request->header('User-Agent');
        $ipAddress = $request->ip();
        
        // In a real app, you might verify a QR code token here
        $user = $request->user();

        $result = $this->attendanceService->recordCheckIn($user, $deviceInfo, $ipAddress);

        if ($result['status'] === 'error') {
            return response()->json(['message' => $result['message']], 400);
        }

        return response()->json($result);
    }

    public function checkOut(Request $request)
    {
        $deviceInfo = $request->header('User-Agent');
        $ipAddress = $request->ip();
        
        $user = $request->user();

        $result = $this->attendanceService->recordCheckOut($user, $deviceInfo, $ipAddress);

        if ($result['status'] === 'error') {
            return response()->json(['message' => $result['message']], 400);
        }

        return response()->json($result);
    }

    public function index(Request $request)
    {
        $query = Attendance::with('user');

        if ($request->has('date')) {
            $query->where('date', $request->date);
        } else {
            $query->where('date', Carbon::today()->toDateString());
        }

        return response()->json($query->get());
    }

    public function manualEntry(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'status' => 'required|string',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'note' => 'nullable|string'
        ]);

        $attendance = Attendance::updateOrCreate(
            ['user_id' => $validated['user_id'], 'date' => $validated['date']],
            [
                'status' => $validated['status'],
                'check_in' => $validated['check_in'],
                'check_out' => $validated['check_out'],
                'note' => $validated['note'],
                'is_manual_entry' => true
            ]
        );

        return response()->json(['message' => 'Attendance recorded manually', 'attendance' => $attendance]);
    }
}
