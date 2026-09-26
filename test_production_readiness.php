<?php

echo "========================================================\n";
echo "MADHAV NETRALAYA - PRODUCTION READINESS VERIFICATION\n";
echo "========================================================\n\n";

$passed = 0;
$failed = 0;

function assertCondition($name, $condition, $details = '') {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] $name\n";
        if ($details) echo "        $details\n";
        $passed++;
    } else {
        echo " [FAIL] $name\n";
        if ($details) echo "        $details\n";
        $failed++;
    }
}

// 1. Check Favicon & Static Assets in both packages
$laravelFavicon = file_exists(__DIR__ . '/../laravel/public/favicon.svg');
$deployFavicon = file_exists(__DIR__ . '/package/public/favicon.svg');
assertCondition("Favicon SVG exists in laravel/public", $laravelFavicon);
assertCondition("Favicon SVG exists in deployment/package/public", $deployFavicon);

$laravelRobots = file_exists(__DIR__ . '/../laravel/public/robots.txt');
$deployRobots = file_exists(__DIR__ . '/package/public/robots.txt');
assertCondition("Robots.txt exists in laravel/public", $laravelRobots);
assertCondition("Robots.txt exists in deployment/package/public", $deployRobots);

// 2. Test Local Server Endpoints (http://127.0.0.1:8000)
echo "\n--- Testing HTTP Endpoints against Live Server ---\n";

// A. Robots.txt
$ch = curl_init("http://127.0.0.1:8000/robots.txt");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCondition("GET /robots.txt returns HTTP 200", $httpCode === 200, "HTTP Code: $httpCode");
assertCondition("Robots.txt contains Sitemap directive", strpos($res, 'sitemap.xml') !== false);
assertCondition("Robots.txt protects admin & API", strpos($res, 'Disallow: /admin') !== false && strpos($res, 'Disallow: /api/') !== false);

// B. Sitemap.xml
$ch = curl_init("http://127.0.0.1:8000/sitemap.xml");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCondition("GET /sitemap.xml returns HTTP 200", $httpCode === 200, "HTTP Code: $httpCode");
assertCondition("Sitemap.xml contains valid XML urlset", strpos($res, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">') !== false);
assertCondition("Sitemap contains core hospital pages", strpos($res, '/departments') !== false && strpos($res, '/doctors') !== false && strpos($res, '/about') !== false);

// C. Favicon SVG
$ch = curl_init("http://127.0.0.1:8000/favicon.svg");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCondition("GET /favicon.svg returns HTTP 200", $httpCode === 200, "HTTP Code: $httpCode");
assertCondition("Favicon is valid SVG content", strpos($res, '<svg') !== false);

// D. Test dynamic CMS State
$ch = curl_init("http://127.0.0.1:8000/api/state");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$state = json_decode($res, true);
assertCondition("GET /api/state returns HTTP 200 JSON", $httpCode === 200 && is_array($state));
assertCondition("Database doctors count preserved", isset($state['doctors']) && count($state['doctors']) > 0, "Found " . count($state['doctors'] ?? []) . " doctors");
assertCondition("Database customPages count preserved", isset($state['customPages']) && count($state['customPages']) > 0, "Found " . count($state['customPages'] ?? []) . " custom pages");

// E. Verify Database Data Integrity
echo "\n--- Verifying SQLite Databases ---\n";
$mainDb = new PDO('sqlite:' . __DIR__ . '/../database.db');
$mainCount = $mainDb->query("SELECT length(value) as len FROM state_store WHERE key = 'state'")->fetch(PDO::FETCH_ASSOC);

$pkgDb = new PDO('sqlite:' . __DIR__ . '/package/database.db');
$pkgCount = $pkgDb->query("SELECT length(value) as len FROM state_store WHERE key = 'state'")->fetch(PDO::FETCH_ASSOC);

assertCondition("Main project database.db state_store is intact", !empty($mainCount['len']), "Size: {$mainCount['len']} bytes");
assertCondition("Deployment package database.db state_store is intact", !empty($pkgCount['len']), "Size: {$pkgCount['len']} bytes");

echo "\n========================================================\n";
echo "VERIFICATION SUMMARY: $passed PASSED, $failed FAILED\n";
echo "========================================================\n";
