<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RazorpayController
{
    /**
     * Mirrors Express POST /api/razorpay/create-order
     * Connects to Razorpay Test API to generate a valid order.
     */
    public function createOrder(Request $request)
    {
        try {
            $amount = $request->input('amount');
            $currency = $request->input('currency', 'INR');
            $notes = $request->input('notes', []);

            // Use the exact amount calculation as server.ts (amount * 100 or default 10000 * 100)
            $orderAmount = ($amount ?: 10000) * 100;
            $receipt = 'rcpt_tender_' . (int)(microtime(true) * 1000);

            // Fetch credentials
            // Attempt to get from env first
            $keyId = env('VITE_RAZORPAY_KEY_ID') ?: env('RAZORPAY_KEY_ID');
            $keySecret = env('RAZORPAY_KEY_SECRET');

            // Do not proceed without credentials if we want to hit the real Razorpay API
            if (empty($keyId) || empty($keySecret)) {
                return response()->json([
                    'error' => 'Razorpay credentials are not configured on the server. Live test blocked.'
                ], 500);
            }

            // Call Razorpay API natively without installing external SDK
            $response = Http::withBasicAuth($keyId, $keySecret)
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount' => $orderAmount,
                    'currency' => $currency,
                    'receipt' => $receipt,
                    'notes' => is_array($notes) ? $notes : ['note' => $notes]
                ]);

            if ($response->successful()) {
                $orderData = $response->json();
                
                return response()->json([
                    'success' => true,
                    'order' => [
                        'id' => $orderData['id'],
                        'amount' => $orderData['amount'],
                        'currency' => $orderData['currency'],
                        'receipt' => $orderData['receipt'],
                        'status' => $orderData['status'],
                        'notes' => $notes
                    ],
                    'keyId' => $keyId
                ]);
            }

            // Error from Razorpay API
            $errorData = $response->json();
            $errorMessage = $errorData['error']['description'] ?? 'Razorpay API rejected the request';
            
            return response()->json([
                'error' => "Razorpay API Error: " . $errorMessage
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create Razorpay order: ' . $e->getMessage()
            ], 500);
        }
    }
}
