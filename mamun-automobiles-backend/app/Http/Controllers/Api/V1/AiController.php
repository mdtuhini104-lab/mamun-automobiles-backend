<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AiPredictionService;
use App\Services\AiAutomationService;
use Illuminate\Http\Request;

class AiController extends Controller
{
    protected $predictionService;
    protected $automationService;

    public function __construct(AiPredictionService $predictionService, AiAutomationService $automationService)
    {
        $this->predictionService = $predictionService;
        $this->automationService = $automationService;
    }

    public function dashboard()
    {
        return response()->json([
            'predictions' => $this->predictionService->getRevenueForecast(),
            'anomalies' => 0,
            'recommendations' => 5
        ]);
    }

    public function runAutomation()
    {
        return response()->json($this->automationService->runAutomations());
    }
}
