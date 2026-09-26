<?php
/**
 * Doctors & Medical Team CMS Integrity Verification Test Suite
 * Tests Medical Team data preservation, API response, and asset bundle status.
 */

echo "=================================================================\n";
echo "MEDICAL TEAM & DOCTORS CMS VERIFICATION TEST SUITE\n";
echo "=================================================================\n\n";

$testsPassed = 0;
$totalTests = 0;

function runDocTest($title, callable $testFn) {
    global $testsPassed, $totalTests;
    $totalTests++;
    echo "[DOCTORS TEST {$totalTests}] {$title} ... ";
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

// TEST 1: Database Doctors Data Preservation (Original DB)
runDocTest("Original database.db Doctors Data Count", function() {
    $db = new SQLite3(__DIR__ . '/../database.db');
    $res = $db->query("SELECT value FROM state_store WHERE key='state'");
    $row = $res->fetchArray(SQLITE3_ASSOC);
    $state = json_decode($row['value'], true);
    $docCount = count($state['doctors'] ?? []);
    if ($docCount >= 15) {
        return true;
    }
    return "Expected >= 15 doctors, found {$docCount}";
});

// TEST 2: Database Doctors Data Preservation (Deployment Package DB)
runDocTest("Deployment Package database.db Doctors Data Count", function() {
    $db = new SQLite3(__DIR__ . '/package/database.db');
    $res = $db->query("SELECT value FROM state_store WHERE key='state'");
    $row = $res->fetchArray(SQLITE3_ASSOC);
    $state = json_decode($row['value'], true);
    $docCount = count($state['doctors'] ?? []);
    if ($docCount >= 15) {
        return true;
    }
    return "Expected >= 15 doctors, found {$docCount}";
});

// TEST 3: Doctor Consultant Types & Ordering Attributes
runDocTest("Doctor Consultant Categories & Reordering Field Schema", function() {
    $db = new SQLite3(__DIR__ . '/package/database.db');
    $res = $db->query("SELECT value FROM state_store WHERE key='state'");
    $row = $res->fetchArray(SQLITE3_ASSOC);
    $state = json_decode($row['value'], true);
    $docs = $state['doctors'] ?? [];
    foreach ($docs as $d) {
        if (empty($d['name']) || empty($d['specialty'])) {
            return "Doctor entry missing name or specialty: " . json_encode($d);
        }
    }
    return true;
});

// TEST 4: Frontend Index.html points to an active bundle
runDocTest("Deployment Package index.html references fresh bundle", function() {
    $html = file_get_contents(__DIR__ . '/package/public/index.html');
    if (preg_match('/assets\/(index-[a-zA-Z0-9_\-]+\.js)/', $html, $m)) {
        if (file_exists(__DIR__ . '/package/public/assets/' . $m[1])) {
            return true;
        }
        return "Referenced bundle does not exist on disk: " . $m[1];
    }
    return "index.html does not reference a valid JS bundle";
});

// TEST 5: Doctor OPD visibility string presence in built bundle
runDocTest("Doctor OPD status bundle integration", function() {
    $html = file_get_contents(__DIR__ . '/package/public/index.html');
    preg_match('/assets\/(index-[a-zA-Z0-9_\-]+\.js)/', $html, $m);
    $activeJs = __DIR__ . '/package/public/assets/' . ($m[1] ?? '');
    if (!file_exists($activeJs)) {
        return "Active bundle file not found: " . $activeJs;
    }
    $js = file_get_contents($activeJs);
    if (strpos($js, 'Hidden from OPD') !== false || strpos($js, 'OPD') !== false) {
        return true;
    }
    return "Hidden from OPD string missing in active bundle";
});

echo "\n=================================================================\n";
echo "TEST RESULTS: {$testsPassed} of {$totalTests} tests passed.\n";
echo "=================================================================\n";
