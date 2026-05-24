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
    public function getAll(array $filters = [])
    {
        $query = Invoice::with(['customer', 'jobCard']);
        
        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }
        
        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        
        // Search
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        $allowedSorts = ['invoice_number', 'grand_total', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
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

    public function getTotals(array $filters = []): array
    {
        $query = \App\Models\Invoice::query();

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
        }

        return [
            'grand_total' => (float) $query->sum('grand_total'),
            'paid_amount' => (float) $query->sum('paid_amount'),
            'due_amount' => (float) $query->sum('due_amount'),
        ];
    }
}
