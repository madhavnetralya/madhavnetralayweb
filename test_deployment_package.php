<?php
/**
 * Automated Verification & Functionality Test Suite for Deployment Package
 * Runs strictly against deployment/package/ using deployment/package/database.db
 */

require_once __DIR__ . '/package/vendor/autoload.php';

$app = require_once __DIR__ . '/package/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=================================================================\n";
echo "MADHAV NETRALAYA - DEPLOYMENT PACKAGE VERIFICATION TEST SUITE\n";
echo "=================================================================\n\n";

$testsPassed = 0;
$totalTests = 0;

function runTest($title, callable $testFn) {
    global $testsPassed, $totalTests;
    $totalTests++;
    echo "[TEST {$totalTests}] {$title} ... ";
    try {
        $result = $testFn();
        if ($result === true) {
            echo "PASSED\n";
            $testsPassed++;
        } else {
            echo "FAILED (" . $result . ")\n";
        }
    } catch (\Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

// TEST 1: Health check endpoint
runTest("Health Check (/up)", function() use ($kernel) {
    $request = Illuminate\Http\Request::create('/up', 'GET');
    $response = $kernel->handle($request);
    return $response->getStatusCode() === 200 ? true : "HTTP " . $response->getStatusCode();
});

// TEST 2: State Store API
runTest("State Store API (GET /api/state)", function() use ($kernel) {
    $request = Illuminate\Http\Request::create('/api/state', 'GET');
    $response = $kernel->handle($request);
    if ($response->getStatusCode() !== 200) {
        return "HTTP " . $response->getStatusCode();
    }
    $data = json_decode($response->getContent(), true);
    if (!isset($data['customPages']) || count($data['customPages']) < 20) {
        return "customPages count low or missing";
    }
    if (!isset($data['doctors']) || count($data['doctors']) < 5) {
        return "doctors count low or missing";
    }
    return true;
});

// TEST 3: Eye Consultation Form Submission
runTest("Eye Consultation Enquiry Submission (POST /api/eye-consultation-enquiries)", function() use ($kernel) {
    $payload = [
        'name' => 'Deployment Test Patient',
        'mobile' => '9876543210',
        'concern' => 'Automated Deployment Health Verification'
    ];
    $request = Illuminate\Http\Request::create('/api/eye-consultation-enquiries', 'POST', $payload);
    $response = $kernel->handle($request);
    if ($response->getStatusCode() !== 200 && $response->getStatusCode() !== 201) {
        return "HTTP " . $response->getStatusCode() . " - " . $response->getContent();
    }
    return true;
});

// TEST 4: Patient Feedback Form Submission
runTest("Patient Feedback Submission (POST /api/feedback)", function() use ($kernel) {
    $payload = [
        'name' => 'Deployment Tester',
        'mobile' => '9876543210',
        'email' => 'test@madhavnetralaya.org',
        'rating' => 5,
        'comments' => 'Verified deployment package functionality.'
    ];
    $request = Illuminate\Http\Request::create('/api/feedback', 'POST', $payload);
    $response = $kernel->handle($request);
    if ($response->getStatusCode() !== 200 && $response->getStatusCode() !== 201) {
        return "HTTP " . $response->getStatusCode() . " - " . $response->getContent();
    }
    return true;
});

// TEST 5: State Anti-Regression Guard
runTest("State Anti-Regression Guard (Rejecting Destructive Wipe)", function() use ($kernel) {
    $payload = [
        'customPages' => [], // Destructive empty pages
        'doctors' => []      // Destructive empty doctors
    ];
    $request = Illuminate\Http\Request::create('/api/state', 'POST', $payload);
    $response = $kernel->handle($request);
    if ($response->getStatusCode() === 422) {
        return true;
    }
    return "Expected HTTP 422 but got HTTP " . $response->getStatusCode();
});

// TEST 6: SPA Web Route Fallback
runTest("SPA Web Route Fallback (GET /doctors/specialists)", function() use ($kernel) {
    $request = Illuminate\Http\Request::create('/doctors/specialists', 'GET');
    $response = $kernel->handle($request);
    if ($response->getStatusCode() === 200 && strpos($response->getContent(), 'root') !== false) {
        return true;
    }
    return "HTTP " . $response->getStatusCode();
});

echo "\n=================================================================\n";
echo "TEST RESULTS: {$testsPassed} of {$totalTests} tests passed.\n";
echo "=================================================================\n";
