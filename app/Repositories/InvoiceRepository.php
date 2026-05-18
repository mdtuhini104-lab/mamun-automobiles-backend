<?php

namespace App\Repositories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;

class InvoiceRepository extends BaseRepository
{
    /**
     * Create a new invoice.
     */
    public function create(array $data): Invoice
    {
        return Invoice::create($data);
    }

    /**
     * Get all invoices with optional filters.
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Invoice::with(['customer', 'jobCard']);
        
        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }
        
        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        
        return $query->get();
    }

    /**
     * Find invoice by ID.
     */
    public function findById(int $id): ?Invoice
    {
        return Invoice::with(['customer', 'jobCard', 'items.part'])->find($id);
    }

    /**
     * Get due invoices by customer.
     */
    public function getDueByCustomer(int $customerId): Collection
    {
        return Invoice::where('customer_id', $customerId)
            ->where('payment_status', '!=', 'paid')
            ->get();
    }

    /**
     * Update an invoice.
     */
    public function update(int $id, array $data): bool
    {
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return false;
        }
        return $invoice->update($data);
    }

    /**
     * Delete an invoice.
     */
    public function delete(int $id): bool
    {
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return false;
        }
        return $invoice->delete();
    }
}
