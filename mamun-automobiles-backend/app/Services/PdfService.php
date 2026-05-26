<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Invoice;

class PdfService
{
    /**
     * Get the PDF instance for an invoice.
     */
    public function getInvoicePdf(Invoice $invoice)
    {
        // Eager load relationships to prevent N+1 queries
        $invoice->load(['customer', 'items']);
        
        return Pdf::loadView('pdf.invoice', ['invoice' => $invoice]);
    }
}
