<?php

namespace App\Services;

class PdfReportService
{
    public function generate($view, $data, $filename = 'report.pdf')
    {
        // Using dompdf or snappy internally
        // Return file path or direct download
        return "storage/reports/" . $filename;
    }
}
