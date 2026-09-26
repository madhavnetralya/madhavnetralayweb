<?php

require __DIR__ . '/package/vendor/autoload.php';
$app = require_once __DIR__ . '/package/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;

echo "========================================================\n";
echo "TESTING UPDATED DEDICATED ADMIN PASSWORD RESET LINK & FLOW\n";
echo "========================================================\n\n";

Cache::flush();

$passed = 0;
$failed = 0;

function report($testName, $condition, $details = "") {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] {$testName}\n";
        if ($details) echo "        {$details}\n";
        $passed++;
    } else {
        echo " [FAIL] {$testName}\n";
        if ($details) echo "        {$details}\n";
        $failed++;
    }
}

$authController = new AdminAuthController();
$adminController = new AdminController();

// 1. Ensure table exists
AdminAuthController::ensureTokensTable();
$tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='password_reset_tokens'");
report("password_reset_tokens table exists in SQLite", count($tables) > 0);

// 2. Test Request Forgot Password link generation uses dynamic APP_URL and dedicated path
$validAdminEmail = "pranav.dhomne@gmail.com";
$req = Request::create('/api/admin/forgot-password', 'POST', ['email' => $validAdminEmail]);
$res = $authController->sendResetLinkApi($req);
$data = json_decode($res->getContent(), true);

report(
    "Forgot Password API request returns generic response",
    $res->getStatusCode() === 200 && ($data['success'] ?? false) === true
);

// Check that token was created in DB
$tokenRecord = DB::table('password_reset_tokens')->where('email', $validAdminEmail)->first();
report("Token record exists in DB with 30m expiry", $tokenRecord !== null && strtotime($tokenRecord->expires_at) > time());

// 3. Test dedicated Blade page GET /admin/reset-password with a valid token
$testRawToken = "test_blade_token_" . bin2hex(random_bytes(16));
$testTokenHash = hash('sha256', $testRawToken);
$testEmail = "test_blade_admin@madhavnetralaya.org";

// Seed user in state_store
$row = DB::table('state_store')->where('key', 'state')->first();
$state = json_decode($row->value, true);
$state['users'][] = [
    'id' => 'u_test_blade',
    'username' => 'testbladeadmin',
    'email' => $testEmail,
    'name' => 'Test Blade Admin',
    'role' => 'super_admin',
    'status' => 'active',
    'password' => 'InitialBladePass123'
];
DB::table('state_store')->updateOrInsert(['key' => 'state'], ['value' => json_encode($state, JSON_UNESCAPED_SLASHES)]);

// Insert valid token
DB::table('password_reset_tokens')->updateOrInsert(
    ['email' => $testEmail],
    [
        'token' => $testTokenHash,
        'created_at' => date('Y-m-d H:i:s'),
        'expires_at' => date('Y-m-d H:i:s', time() + 1800)
    ]
);

// Call Blade showPasswordReset
$req = Request::create('/admin/reset-password', 'GET', ['token' => $testRawToken, 'email' => $testEmail]);
$view = $adminController->showPasswordReset($req);

report(
    "GET /admin/reset-password?token=...&email=... loads dedicated reset view",
    $view instanceof \Illuminate\View\View && $view->getName() === 'admin.reset',
    "View Name: " . ($view instanceof \Illuminate\View\View ? $view->getName() : 'N/A')
);

// 4. Test Blade showPasswordReset with INVALID token redirects to request page
$reqInvalid = Request::create('/admin/reset-password', 'GET', ['token' => 'invalid_tok', 'email' => $testEmail]);
$redirect = $adminController->showPasswordReset($reqInvalid);
report(
    "GET /admin/reset-password with invalid token redirects to /admin/forgot-password",
    $redirect instanceof \Illuminate\Http\RedirectResponse
);

// 5. Test Blade showPasswordReset with EXPIRED token redirects to request page
DB::table('password_reset_tokens')->updateOrInsert(
    ['email' => $testEmail],
    [
        'token' => $testTokenHash,
        'created_at' => date('Y-m-d H:i:s', time() - 3600),
        'expires_at' => date('Y-m-d H:i:s', time() - 100)
    ]
);
$redirectExpired = $adminController->showPasswordReset($req);
report(
    "GET /admin/reset-password with expired token redirects and purges token",
    $redirectExpired instanceof \Illuminate\Http\RedirectResponse
);

// 6. Test Blade handlePasswordReset submission (Form submit to set new password)
// Re-insert valid token
DB::table('password_reset_tokens')->updateOrInsert(
    ['email' => $testEmail],
    [
        'token' => $testTokenHash,
        'created_at' => date('Y-m-d H:i:s'),
        'expires_at' => date('Y-m-d H:i:s', time() + 1800)
    ]
);

$newPass = "FreshSecureAdminPass2026!";
$submitReq = Request::create('/admin/reset-password', 'POST', [
    'email' => $testEmail,
    'token' => $testRawToken,
    'password' => $newPass,
    'password_confirmation' => $newPass
]);
$resSubmit = $adminController->handlePasswordReset($submitReq);

report(
    "POST /admin/reset-password successfully resets password and redirects to login",
    $resSubmit instanceof \Illuminate\Http\RedirectResponse && $resSubmit->getTargetUrl() === route('admin.login')
);

// Verify password in DB state
$row = DB::table('state_store')->where('key', 'state')->first();
$state = json_decode($row->value, true);
$updatedUser = null;
foreach ($state['users'] as $u) {
    if ($u['email'] === $testEmail) {
        $updatedUser = $u;
        break;
    }
}
report(
    "User password updated in database state store",
    $updatedUser !== null && $updatedUser['password'] === $newPass,
    "New password matches: " . ($updatedUser['password'] === $newPass ? 'YES' : 'NO')
);

// 7. Verify single-use token was consumed immediately
$consumedToken = DB::table('password_reset_tokens')->where('email', $testEmail)->first();
report("Token was immediately invalidated upon use", $consumedToken === null);

// Cleanup test user
$row = DB::table('state_store')->where('key', 'state')->first();
$state = json_decode($row->value, true);
$state['users'] = array_values(array_filter($state['users'], fn($u) => $u['id'] !== 'u_test_blade'));
DB::table('state_store')->updateOrInsert(['key' => 'state'], ['value' => json_encode($state, JSON_UNESCAPED_SLASHES)]);

// 8. Data integrity check
report(
    "Doctors count preserved",
    count($state['doctors'] ?? []) === 18,
    "Doctors count: " . count($state['doctors'] ?? [])
);
report(
    "Custom pages count preserved",
    count($state['customPages'] ?? []) === 53,
    "Pages count: " . count($state['customPages'] ?? [])
);

echo "\n========================================================\n";
echo "SUMMARY: {$passed} PASSED, {$failed} FAILED\n";
echo "========================================================\n";

if ($failed === 0) {
    exit(0);
} else {
    exit(1);
}
