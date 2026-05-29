<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class OnboardingController extends Controller
{
    /**
     * Import customers from a CSV file.
     */
    public function importCustomers(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $tenantId = $request->user()->tenant_id;
        $branchId = $request->user()->branch_id ?? DB::table('branches')->where('tenant_id', $tenantId)->value('id');

        if (!$branchId) {
            return response()->json([
                'success' => false,
                'message' => 'No branch context established. Register a branch first.'
            ], 400);
        }

        $file = $request->file('file');
        $csvData = file_get_contents($file->getRealPath());
        $rows = array_map('str_getcsv', explode("\n", trim($csvData)));
        $header = array_shift($rows);

        // Map headers to indices
        $indices = array_flip($header);
        $nameIndex = $indices['name'] ?? null;
        $emailIndex = $indices['email'] ?? null;
        $phoneIndex = $indices['phone'] ?? null;
        $addressIndex = $indices['address'] ?? null;

        if ($nameIndex === null || $phoneIndex === null) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid CSV format. Name and Phone columns are required.'
            ], 422);
        }

        try {
            $importedCount = 0;
            DB::transaction(function () use ($rows, $tenantId, $branchId, $nameIndex, $emailIndex, $phoneIndex, $addressIndex, &$importedCount) {
                foreach ($rows as $row) {
                    if (count($row) < 2) continue;

                    $name = $row[$nameIndex] ?? '';
                    $phone = $row[$phoneIndex] ?? '';
                    $email = $emailIndex !== null ? ($row[$emailIndex] ?? '') : '';
                    $address = $addressIndex !== null ? ($row[$addressIndex] ?? '') : '';

                    if (empty($name) || empty($phone)) continue;

                    DB::table('customers')->insert([
                        'tenant_id' => $tenantId,
                        'branch_id' => $branchId,
                        'name' => $name,
                        'phone' => $phone,
                        'email' => $email,
                        'address' => $address,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $importedCount++;
                }

                // Audit log
                DB::table('audit_logs')->insert([
                    'tenant_id' => $tenantId,
                    'user_id' => auth()->id(),
                    'action' => 'import_customers_csv',
                    'module' => 'onboarding',
                    'details' => json_encode(['imported_count' => $importedCount]),
                    'ip_address' => request()->ip(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$importedCount} customers."
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process CSV file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import parts inventory from a CSV file.
     */
    public function importParts(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $tenantId = $request->user()->tenant_id;
        $branchId = $request->user()->branch_id ?? DB::table('branches')->where('tenant_id', $tenantId)->value('id');

        if (!$branchId) {
            return response()->json([
                'success' => false,
                'message' => 'No branch context established. Register a branch first.'
            ], 400);
        }

        $file = $request->file('file');
        $csvData = file_get_contents($file->getRealPath());
        $rows = array_map('str_getcsv', explode("\n", trim($csvData)));
        $header = array_shift($rows);

        $indices = array_flip($header);
        $nameIndex = $indices['name'] ?? null;
        $skuIndex = $indices['sku'] ?? null;
        $priceIndex = $indices['price'] ?? null;
        $qtyIndex = $indices['quantity'] ?? null;

        if ($nameIndex === null || $skuIndex === null || $priceIndex === null) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid CSV format. Name, SKU, and Price columns are required.'
            ], 422);
        }

        try {
            $importedCount = 0;
            DB::transaction(function () use ($rows, $tenantId, $branchId, $nameIndex, $skuIndex, $priceIndex, $qtyIndex, &$importedCount) {
                foreach ($rows as $row) {
                    if (count($row) < 3) continue;

                    $name = $row[$nameIndex] ?? '';
                    $sku = $row[$skuIndex] ?? '';
                    $price = $row[$priceIndex] ?? 0.00;
                    $qty = $qtyIndex !== null ? ($row[$qtyIndex] ?? 0) : 0;

                    if (empty($name) || empty($sku)) continue;

                    DB::table('parts')->insert([
                        'tenant_id' => $tenantId,
                        'branch_id' => $branchId,
                        'name' => $name,
                        'sku' => $sku,
                        'price' => $price,
                        'stock_quantity' => $qty,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $importedCount++;
                }

                // Audit log
                DB::table('audit_logs')->insert([
                    'tenant_id' => $tenantId,
                    'user_id' => auth()->id(),
                    'action' => 'import_parts_csv',
                    'module' => 'onboarding',
                    'details' => json_encode(['imported_count' => $importedCount]),
                    'ip_address' => request()->ip(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$importedCount} parts into stock."
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process CSV file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate sandbox demo data for testing system workflows.
     */
    public function generateDemoData(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $branchId = $request->user()->branch_id ?? DB::table('branches')->where('tenant_id', $tenantId)->value('id');

        if (!$branchId) {
            return response()->json([
                'success' => false,
                'message' => 'No branch context established. Register a branch first.'
            ], 400);
        }

        try {
            DB::transaction(function () use ($tenantId, $branchId) {
                // Seed mock customer
                $customerId = DB::table('customers')->insertGetId([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'name' => 'Demo Customer Accounts',
                    'phone' => '+0170000000',
                    'email' => 'demo@mamunautomobiles.com',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Seed mock vehicle
                $vehicleId = DB::table('vehicles')->insertGetId([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'customer_id' => $customerId,
                    'plate_no' => 'DEMO-889',
                    'make' => 'Toyota',
                    'model' => 'Axio',
                    'year' => '2016',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Seed mock parts
                $partId = DB::table('parts')->insertGetId([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'name' => 'Synthetic Engine Oil 5W-30',
                    'sku' => 'OIL-SYN-5W30',
                    'price' => 45.00,
                    'stock_quantity' => 100,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Seed mock job card
                $jobCardId = DB::table('job_cards')->insertGetId([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'vehicle_id' => $vehicleId,
                    'status' => 'draft',
                    'odometer' => 54000,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Seed mock quotation
                $quotationId = DB::table('quotations')->insertGetId([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'job_card_id' => $jobCardId,
                    'grand_total' => 120.00,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::table('quotation_items')->insert([
                    'quotation_id' => $quotationId,
                    'part_name' => 'Synthetic Engine Oil 5W-30',
                    'sku' => 'OIL-SYN-5W30',
                    'quantity' => 1,
                    'unit_price' => 45.00,
                    'subtotal' => 45.00,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Audit log
                DB::table('audit_logs')->insert([
                    'tenant_id' => $tenantId,
                    'user_id' => auth()->id(),
                    'action' => 'generate_sandbox_demo_data',
                    'module' => 'onboarding',
                    'details' => json_encode(['customer_id' => $customerId, 'job_card_id' => $jobCardId]),
                    'ip_address' => request()->ip(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Sandbox workflow demo data generated successfully.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate demo data: ' . $e->getMessage()
            ], 500);
        }
    }
}
