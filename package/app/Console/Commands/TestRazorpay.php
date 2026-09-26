<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Http\Controllers\RazorpayController;

class TestRazorpay extends Command
{
    protected $signature = 'test:razorpay';
    protected $description = 'Safely verifies Razorpay implementation offline using mocked HTTP facade';

    public function handle()
    {
        // 1. SETUP MOCK CREDENTIALS IN MEMORY ONLY
        putenv('VITE_RAZORPAY_KEY_ID=rzp_test_mock_id');
        putenv('RAZORPAY_KEY_SECRET=rzp_test_mock_secret');

        $this->info("1. Mocking Razorpay API Success Response...");
        
        // 2. MOCK THE HTTP FACADE
        Http::fake([
            'api.razorpay.com/v1/orders' => Http::sequence()
                ->push([
                    'id' => 'order_mock_123',
                    'entity' => 'order',
                    'amount' => 1500000,
                    'currency' => 'INR',
                    'receipt' => 'rcpt_mock_1',
                    'status' => 'created'
                ], 200)
                ->push([
                    'error' => [
                        'code' => 'BAD_REQUEST_ERROR',
                        'description' => 'The amount must be a minimum of INR 1.00',
                        'source' => 'business',
                        'step' => 'payment_initiation',
                        'reason' => 'input_validation_failed'
                    ]
                ], 400)
        ]);

        // 3. CREATE DUMMY REQUEST
        $request = Request::create('/api/razorpay/create-order', 'POST', [
            'amount' => 15000,
            'currency' => 'INR',
            'notes' => 'Offline Test Note'
        ]);

        // 4. INVOKE CONTROLLER
        $controller = new RazorpayController();
        $response = $controller->createOrder($request);
        $responseData = json_decode($response->getContent(), true);

        // 5. VERIFY SUCCESS RESPONSE
        if ($response->getStatusCode() === 200 && $responseData['success'] === true && $responseData['order']['id'] === 'order_mock_123') {
            $this->info("SUCCESS: Laravel correctly requested Razorpay and transformed the successful response!");
            $this->line(json_encode($responseData, JSON_PRETTY_PRINT));
        } else {
            $this->error("FAILED to process success response.");
        }

        $this->info("\n2. Mocking Razorpay API Error Response...");
        
        // (Already mocked via sequence above)

        $failRequest = Request::create('/api/razorpay/create-order', 'POST', [
            'amount' => 0, // Invalid amount triggers fail
        ]);

        $failResponse = $controller->createOrder($failRequest);
        $failData = json_decode($failResponse->getContent(), true);
        
        $this->line("Status Code: " . $failResponse->getStatusCode());
        $this->line(json_encode($failData, JSON_PRETTY_PRINT));

        if ($failResponse->getStatusCode() === 400 && str_contains($failData['error'], 'The amount must be a minimum of INR 1.00')) {
            $this->info("SUCCESS: Laravel correctly caught and propagated the safe error message.");
            $this->line(json_encode($failData, JSON_PRETTY_PRINT));
        } else {
            $this->error("FAILED to process error response safely.");
        }

        // 7. VERIFY NO SECRETS LEAKED
        if (str_contains($failResponse->getContent(), 'rzp_test_mock_secret')) {
            $this->error("SECURITY BREACH: The secret was leaked in the response!");
        } else {
            $this->info("SECURITY VERIFIED: No secrets leaked in error response.");
        }
    }
}
