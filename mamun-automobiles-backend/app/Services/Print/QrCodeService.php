<?php

namespace App\Services\Print;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    /**
     * Generate an SVG QR code.
     *
     * @param string $data
     * @param int $size
     * @return string
     */
    public function generateSvg($data, $size = 100)
    {
        return QrCode::size($size)->generate($data);
    }
    
    /**
     * Generate a Base64 encoded image for PDF embedding.
     *
     * @param string $data
     * @param int $size
     * @return string
     */
    public function generateBase64($data, $size = 100)
    {
        // For simple-qrcode, to get png base64
        $image = QrCode::format('png')->size($size)->generate($data);
        return 'data:image/png;base64,' . base64_encode($image);
    }
}
