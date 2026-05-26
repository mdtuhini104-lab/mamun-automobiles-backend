<?php

namespace App\Services\Print;

use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorSVG;

class BarcodeService
{
    /**
     * Generate SVG barcode.
     *
     * @param string $data
     * @param string $type
     * @return string
     */
    public function generateSvg($data)
    {
        $generator = new BarcodeGeneratorSVG();
        return $generator->getBarcode($data, $generator::TYPE_CODE_128);
    }

    /**
     * Generate Base64 PNG barcode.
     *
     * @param string $data
     * @return string
     */
    public function generateBase64($data)
    {
        $generator = new BarcodeGeneratorPNG();
        $image = $generator->getBarcode($data, $generator::TYPE_CODE_128);
        return 'data:image/png;base64,' . base64_encode($image);
    }
}
