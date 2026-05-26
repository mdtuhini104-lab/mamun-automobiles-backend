<?php

namespace App\Repositories;

use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Collection;

class InvoiceItemRepository extends BaseRepository
{
    /**
     * Create a new invoice item.
     */
    public function create(array $data): InvoiceItem
    {
        return InvoiceItem::create($data);
    }

    /**
     * Get items by invoice ID.
     */
    public function getByInvoiceId(int $invoiceId): Collection
    {
        return InvoiceItem::where('invoice_id', $invoiceId)->with('part')->get();
    }
}
