<?php

namespace App\Services\Print;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;

class PdfPrintService
{
    /**
     * Generate a PDF document from a view.
     *
     * @param string $view
     * @param array $data
     * @param string $paperSize
     * @param string $orientation
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generate($view, $data, $paperSize = 'A4', $orientation = 'portrait')
    {
        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper($paperSize, $orientation);
        
        // Optional configuration for utf-8 and specific fonts
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif'
        ]);
        
        return $pdf;
    }

    /**
     * Download a PDF directly.
     *
     * @param string $view
     * @param array $data
     * @param string $filename
     * @param string $paperSize
     * @param string $orientation
     * @return \Illuminate\Http\Response
     */
    public function download($view, $data, $filename, $paperSize = 'A4', $orientation = 'portrait')
    {
        return $this->generate($view, $data, $paperSize, $orientation)->download($filename);
    }

    /**
     * Stream a PDF in the browser.
     *
     * @param string $view
     * @param array $data
     * @param string $filename
     * @param string $paperSize
     * @param string $orientation
     * @return \Illuminate\Http\Response
     */
    public function stream($view, $data, $filename, $paperSize = 'A4', $orientation = 'portrait')
    {
        return $this->generate($view, $data, $paperSize, $orientation)->stream($filename);
    }
}
