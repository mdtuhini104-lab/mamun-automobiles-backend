<?php

namespace App\Services;

class SslAutomationService
{
    public function generateSsl($domain)
    {
        // Certbot integration
        return "SSL Certificate generated for " . $domain;
    }
}
