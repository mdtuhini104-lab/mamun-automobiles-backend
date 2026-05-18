<?php

if (!function_exists('format_currency')) {
    /**
     * Format number to currency.
     */
    function format_currency($amount, $currency = 'USD')
    {
        return $currency . ' ' . number_format($amount, 2);
    }
}
