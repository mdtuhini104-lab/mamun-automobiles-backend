<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Part;
use App\Models\Invoice;

class GlobalSearchService
{
    /**
     * Search across multiple modules.
     */
    public function search(string $query): array
    {
        $results = [];

        // Search Customers
        $customers = Customer::where('name', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        foreach ($customers as $customer) {
            $results[] = [
                'type' => 'customer',
                'id' => $customer->id,
                'title' => $customer->name,
                'subtitle' => $customer->phone,
                'data' => $customer,
            ];
        }

        // Search Vehicles
        $vehicles = Vehicle::where('license_plate', 'like', "%{$query}%")
            ->orWhere('vin', 'like', "%{$query}%")
            ->orWhere('model', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        foreach ($vehicles as $vehicle) {
            $results[] = [
                'type' => 'vehicle',
                'id' => $vehicle->id,
                'title' => $vehicle->model . ' (' . $vehicle->license_plate . ')',
                'subtitle' => 'VIN: ' . $vehicle->vin,
                'data' => $vehicle,
            ];
        }

        // Search Parts
        $parts = Part::where('name', 'like', "%{$query}%")
            ->orWhere('sku', 'like', "%{$query}%")
            ->orWhere('brand', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        foreach ($parts as $part) {
            $results[] = [
                'type' => 'part',
                'id' => $part->id,
                'title' => $part->name,
                'subtitle' => 'SKU: ' . $part->sku . ' | Brand: ' . $part->brand,
                'data' => $part,
            ];
        }

        // Search Invoices
        $invoices = Invoice::where('invoice_number', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        foreach ($invoices as $invoice) {
            $results[] = [
                'type' => 'invoice',
                'id' => $invoice->id,
                'title' => 'Invoice ' . $invoice->invoice_number,
                'subtitle' => 'Status: ' . $invoice->payment_status,
                'data' => $invoice,
            ];
        }

        return $results;
    }
}
