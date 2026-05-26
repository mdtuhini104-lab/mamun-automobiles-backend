<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GeneratePDFInvoiceJob implements ShouldQueue
{
    use Queueable;

    protected $invoiceId;

    /**
     * Create a new job instance.
     */
    public function __construct($invoiceId)
    {
        $this->invoiceId = $invoiceId;
    }

    /**
     * Execute the job.
     */
    public function handle(\App\Services\PdfService $pdfService): void
    {
        // Generate PDF and maybe email or store it.
        // For demonstration, we just call the generation which takes time.
        $pdfService->generateInvoicePdf($this->invoiceId);
    }
}
