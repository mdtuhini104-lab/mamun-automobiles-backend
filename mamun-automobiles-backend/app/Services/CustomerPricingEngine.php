<?php

namespace App\Services;

use App\Models\CustomerPricing;
use App\Models\Part;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JobCardItem;
use Illuminate\Support\Facades\DB;

class CustomerPricingEngine
{
    /**
     * Get negotiated or suggested price for a customer and product/service.
     */
    public function getRateForCustomer(int $customerId, ?int $partId = null, ?string $serviceName = null): float
    {
        $partKey = $partId ?: 'service';
        $srvName = $serviceName ?: 'none';
        $cacheKey = "customer_rate_{$customerId}_{$partKey}_" . md5($srvName);

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () use ($customerId, $partId, $serviceName) {
            // 1. Check customer_pricings table first
            $customRate = CustomerPricing::where('customer_id', $customerId)
                ->where(function ($query) use ($partId, $serviceName) {
                    if ($partId) {
                        $query->where('part_id', $partId);
                    }
                    if ($serviceName) {
                        $query->orWhere('labor_service_name', $serviceName);
                    }
                })
                ->orderBy('effective_date', 'desc')
                ->first();

            if ($customRate) {
                if ($partId && $customRate->custom_price !== null) {
                    return (float) $customRate->custom_price;
                }
                if ($serviceName && $customRate->custom_labor_rate !== null) {
                    return (float) $customRate->custom_labor_rate;
                }
            }

            // 2. Check customer historical invoices / transactions for same item
            if ($partId) {
                $historicalInvoiceItem = InvoiceItem::whereHas('invoice', function ($q) use ($customerId) {
                    $q->where('customer_id', $customerId);
                })
                ->where('part_id', $partId)
                ->orderBy('created_at', 'desc')
                ->first();

                if ($historicalInvoiceItem) {
                    return (float) $historicalInvoiceItem->unit_price;
                }

                // Check job card item history as a backup
                $historicalJobCardItem = JobCardItem::whereHas('jobCard', function ($q) use ($customerId) {
                    $q->where('customer_id', $customerId)->where('service_status', 'completed');
                })
                ->where('part_id', $partId)
                ->orderBy('created_at', 'desc')
                ->first();

                if ($historicalJobCardItem) {
                    return (float) $historicalJobCardItem->unit_price;
                }
            }

            // 3. Fallback to default product/service price
            if ($partId) {
                $part = Part::find($partId);
                return $part ? (float) $part->sale_price : 0.00;
            }

            if ($serviceName) {
                $defaultService = Part::where('name', $serviceName)
                    ->where('is_service', true)
                    ->first();
                    
                if ($defaultService) {
                    return (float) $defaultService->sale_price;
                }
                
                return 1000.00; // default service diagnostic scan rate fallback
            }

            return 0.00;
        });
    }

    /**
     * Store a customer contract price rule.
     */
    public function storeContractRate(array $data): CustomerPricing
    {
        return DB::transaction(function () use ($data) {
            $pricing = CustomerPricing::updateOrCreate(
                [
                    'customer_id' => $data['customer_id'],
                    'part_id' => $data['part_id'] ?? null,
                    'labor_service_name' => $data['labor_service_name'] ?? null,
                ],
                [
                    'custom_price' => $data['custom_price'] ?? null,
                    'custom_labor_rate' => $data['custom_labor_rate'] ?? null,
                    'effective_date' => $data['effective_date'] ?? now()->toDateString(),
                    'notes' => $data['notes'] ?? null,
                ]
            );

            // Clear cache holds for this key specifically to guarantee dynamic safety
            $partKey = $data['part_id'] ?? 'service';
            $srvName = $data['labor_service_name'] ?? 'none';
            \Illuminate\Support\Facades\Cache::forget("customer_rate_" . $data['customer_id'] . "_{$partKey}_" . md5($srvName));

            return $pricing;
        });
    }
}
