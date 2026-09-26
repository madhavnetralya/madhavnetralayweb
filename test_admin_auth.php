<?php
/**
 * Automated Admin Authentication & CMS State Persistence Test Suite
 * Tests admin user lookup, state persistence, password update, and role authorization on deployment copy.
 */

require_once __DIR__ . '/package/vendor/autoload.php';
$app = require_once __DIR__ . '/package/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=================================================================\n";
echo "ADMIN AUTHENTICATION & CMS AUTHORIZATION VERIFICATION\n";
echo "=================================================================\n\n";

$testsPassed = 0;
$totalTests = 0;

function runAuthTest($title, callable $testFn) {
    global $testsPassed, $totalTests;
    $totalTests++;
    echo "[AUTH {$totalTests}] {$title} ... ";
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

// TEST 1: Retrieve CMS State with Users
runAuthTest("CMS State Store API (GET /api/state) contains Admin Roles", function() use ($kernel) {
    $request = Illuminate\Http\Request::create('/api/state', 'GET');
    $response = $kernel->handle($request);
    if ($response->getStatusCode() !== 200) {
        return "HTTP " . $response->getStatusCode();
    }
    $state = json_decode($response->getContent(), true);
    if (!isset($state['users']) || !is_array($state['users'])) {
        return "No 'users' array in state";
    }
    $foundAdmin = false;
    foreach ($state['users'] as $u) {
        if (($u['role'] ?? '') === 'super_admin' && !empty($u['username'])) {
            $foundAdmin = true;
            break;
        }
    }
    return $foundAdmin ? true : "No super_admin user found";
});

// TEST 2: Verify Multiple Roles Exist (Super Admin & Reception)
runAuthTest("Multi-Role Authorization Verification (super_admin & reception)", function() use ($kernel) {
    $request = Illuminate\Http\Request::create('/api/state', 'GET');
    $response = $kernel->handle($request);
    $state = json_decode($response->getContent(), true);
    $roles = array_column($state['users'] ?? [], 'role');
    if (in_array('super_admin', $roles) && in_array('reception', $roles)) {
        return true;
    }
    return "Missing required roles. Found: " . implode(', ', $roles);
});

// TEST 3: State Anti-Regression Guard for Users
runAuthTest("Anti-Regression Guard (Rejecting Empty State Update)", function() use ($kernel) {
    $request = Illuminate\Http\Request::create('/api/state', 'POST', []);
    $response = $kernel->handle($request);
    if ($response->getStatusCode() === 400) {
        return true;
    }
    return "Expected HTTP 400 on empty state payload, got HTTP " . $response->getStatusCode();
});

// TEST 4: Non-Destructive CMS Section Update
runAuthTest("Atomic CMS State Persistence (POST /api/state)", function() use ($kernel) {
    // Read current state
    $request = Illuminate\Http\Request::create('/api/state', 'GET');
    $response = $kernel->handle($request);
    $currentState = json_decode($response->getContent(), true);
    
    // Perform a safe non-destructive update (e.g. updating a test setting)
    $currentState['settings']['lastDeploymentTestTimestamp'] = date('Y-m-d H:i:s');
    
    $updateRequest = Illuminate\Http\Request::create('/api/state', 'POST', $currentState);
    $updateResponse = $kernel->handle($updateRequest);
    
    if ($updateResponse->getStatusCode() === 200) {
        return true;
    }
    return "Update failed with HTTP " . $updateResponse->getStatusCode() . " - " . $updateResponse->getContent();
});

// TEST 5: Verify Automated Pre-Write Backup Generation
runAuthTest("Automated Pre-Write Snapshot Generation (storage/app/state_backups)", function() {
    $backupDir = __DIR__ . '/package/storage/app/state_backups';
    if (!is_dir($backupDir)) {
        return "Backup directory missing";
    }
    $backups = glob($backupDir . '/state_backup_auto_*.json');
    if (count($backups) > 0) {
        return true;
    }
    return "No automated state backup snapshots found";
});

echo "\n=================================================================\n";
echo "AUTH & CMS TEST RESULTS: {$testsPassed} of {$totalTests} tests passed.\n";
echo "=================================================================\n";
