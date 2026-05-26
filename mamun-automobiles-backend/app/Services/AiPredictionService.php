<?php

namespace App\Services;

class AiPredictionService
{
    public function getRevenueForecast()
    {
        return [
            'forecast' => 1500000,
            'confidence' => 85,
            'trend' => 'up'
        ];
    }
}
