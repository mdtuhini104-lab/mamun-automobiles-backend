<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PayrollService;
use App\Models\Payroll;

class PayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|string|size:2',
            'year' => 'required|string|size:4'
        ]);

        $result = $this->payrollService->generateMonthlyPayroll($validated['month'], $validated['year']);

        return response()->json([
            'message' => 'Payroll generation completed',
            'generated_count' => $result['generated_count']
        ]);
    }

    public function index(Request $request)
    {
        $query = Payroll::with('user');

        if ($request->has('month') && $request->has('year')) {
            $query->where('month', $request->month)->where('year', $request->year);
        }

        return response()->json($query->get());
    }

    public function show($id)
    {
        $payroll = Payroll::with(['user', 'user.department', 'user.designation'])->findOrFail($id);
        
        // QR Code generation for Payslip
        // This simulates injecting a QR code for verification on the print template
        $qrUrl = url('/verify/payslip/' . $payroll->id);
        $qrCode = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(100)->generate($qrUrl));
        
        return response()->json([
            'payroll' => $payroll,
            'qr_code' => 'data:image/svg+xml;base64,' . $qrCode
        ]);
    }

    public function approve(Request $request, $id)
    {
        $payroll = Payroll::findOrFail($id);
        $payroll->update(['status' => 'approved']);

        return response()->json(['message' => 'Payroll approved', 'payroll' => $payroll]);
    }

    public function markPaid(Request $request, $id)
    {
        $payroll = Payroll::findOrFail($id);
        $payroll->update(['status' => 'paid', 'payment_date' => now()->toDateString()]);

        return response()->json(['message' => 'Payroll marked as paid', 'payroll' => $payroll]);
    }
}
