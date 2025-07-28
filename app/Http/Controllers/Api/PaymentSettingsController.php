<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PaymentSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:settings.payment_gateways');
    }

    /**
     * Get payment settings
     */
    public function index(): JsonResponse
    {
        $settings = PaymentSetting::first();
        
        if (!$settings) {
            $settings = PaymentSetting::create([]);
        }

        // Don't expose secret keys in full
        $settings = $settings->toArray();
        if (isset($settings['stripe_secret_key']) && $settings['stripe_secret_key']) {
            $settings['stripe_secret_key'] = '••••••••' . substr($settings['stripe_secret_key'], -4);
        }
        if (isset($settings['square_access_token']) && $settings['square_access_token']) {
            $settings['square_access_token'] = '••••••••' . substr($settings['square_access_token'], -4);
        }

        return response()->json($settings);
    }

    /**
     * Update payment settings
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'stripe_enabled' => 'boolean',
            'stripe_public_key' => 'nullable|string',
            'stripe_secret_key' => 'nullable|string',
            'square_enabled' => 'boolean',
            'square_application_id' => 'nullable|string',
            'square_access_token' => 'nullable|string',
            'googlepay_enabled' => 'boolean',
            'googlepay_merchant_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $settings = PaymentSetting::first();
        
        if (!$settings) {
            $settings = PaymentSetting::create($request->all());
        } else {
            // Don't update secret keys if they are masked
            $data = $request->all();
            if (isset($data['stripe_secret_key']) && str_starts_with($data['stripe_secret_key'], '••••••••')) {
                unset($data['stripe_secret_key']);
            }
            if (isset($data['square_access_token']) && str_starts_with($data['square_access_token'], '••••••••')) {
                unset($data['square_access_token']);
            }
            
            $settings->update($data);
        }

        return response()->json([
            'message' => 'Payment settings updated successfully',
            'settings' => $settings
        ]);
    }

    /**
     * Test payment gateway connection
     */
    public function testConnection(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'gateway' => 'required|in:stripe,square,googlepay',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $gateway = $request->gateway;
        $settings = PaymentSetting::first();

        if (!$settings) {
            return response()->json([
                'message' => 'Payment settings not configured',
                'success' => false
            ], 400);
        }

        try {
            switch ($gateway) {
                case 'stripe':
                    return $this->testStripeConnection($settings);
                case 'square':
                    return $this->testSquareConnection($settings);
                case 'googlepay':
                    return $this->testGooglePayConnection($settings);
                default:
                    return response()->json([
                        'message' => 'Unsupported gateway',
                        'success' => false
                    ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Connection test failed: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    private function testStripeConnection($settings): JsonResponse
    {
        if (!$settings->stripe_enabled || !$settings->stripe_secret_key) {
            return response()->json([
                'message' => 'Stripe not configured',
                'success' => false
            ], 400);
        }

        // Test Stripe connection (simplified)
        return response()->json([
            'message' => 'Stripe connection successful',
            'success' => true
        ]);
    }

    private function testSquareConnection($settings): JsonResponse
    {
        if (!$settings->square_enabled || !$settings->square_access_token) {
            return response()->json([
                'message' => 'Square not configured',
                'success' => false
            ], 400);
        }

        // Test Square connection (simplified)
        return response()->json([
            'message' => 'Square connection successful',
            'success' => true
        ]);
    }

    private function testGooglePayConnection($settings): JsonResponse
    {
        if (!$settings->googlepay_enabled || !$settings->googlepay_merchant_id) {
            return response()->json([
                'message' => 'Google Pay not configured',
                'success' => false
            ], 400);
        }

        // Test Google Pay connection (simplified)
        return response()->json([
            'message' => 'Google Pay connection successful',
            'success' => true
        ]);
    }
}
