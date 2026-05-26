<?php

namespace App\Services\Print;

class ThermalPrintService
{
    /**
     * Prepare thermal receipt data for frontend or ESC/POS.
     * Often, thermal receipts are best rendered via HTML frontend using browser print 
     * or a raw text stream. We will generate the HTML view suitable for thermal.
     *
     * @param string $view
     * @param array $data
     * @param string $width (e.g. '58mm' or '80mm')
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function renderHtml($view, $data, $width = '80mm')
    {
        $data['thermal_width'] = $width;
        return view($view, $data);
    }
}
