<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SSLCommerzService;
use App\Services\StripeService;
use App\Services\SubscriptionService;

class WebhookController extends Controller
{
    public function sslcommerzIPN(Request $request, SSLCommerzService $sslService, SubscriptionService $subService)
    {
        if ($sslService->verifyIPN($request->all())) {
            // Auto-renew subscription logic
            $subService->activatePlan($request->input('value_a'), $request->input('value_b'));
            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'failed'], 400);
    }

    public function stripeWebhook(Request $request, StripeService $stripeService)
    {
        if ($stripeService->handleWebhook($request->getContent(), $request->header('Stripe-Signature'))) {
            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'failed'], 400);
    }
}
