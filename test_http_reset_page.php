<?php

$pdo = new PDO('sqlite:' . __DIR__ . '/package/database.db');
$rawToken = 'test_http_token_' . bin2hex(random_bytes(8));
$hash = hash('sha256', $rawToken);
$email = 'pranav.dhomne@gmail.com';
$expiry = date('Y-m-d H:i:s', time() + 1800);

$stmt = $pdo->prepare('INSERT OR REPLACE INTO password_reset_tokens (email, token, created_at, expires_at) VALUES (?, ?, datetime("now"), ?)');
$stmt->execute([$email, $hash, $expiry]);

$url = 'http://127.0.0.1:8000/admin/reset-password?token=' . $rawToken . '&email=' . urlencode($email);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Testing URL: {$url}\n";
echo "HTTP Status: {$httpCode}\n";
echo "Contains 'Set New Administrator Password': " . (str_contains($html, 'Set New Administrator Password') ? 'YES' : 'NO') . "\n";
echo "Contains Verified Email: " . (str_contains($html, $email) ? 'YES' : 'NO') . "\n";
echo "Contains Password Field: " . (str_contains($html, 'id="password"') ? 'YES' : 'NO') . "\n";

if ($httpCode === 200 && str_contains($html, 'Set New Administrator Password') && str_contains($html, $email)) {
    echo "\n[PASS] Dedicated Admin Reset Password screen opens and renders successfully via HTTP!\n";
    exit(0);
} else {
    echo "\n[FAIL] Failed to render reset screen.\n";
    exit(1);
}
