<?php

echo "========================================================\n";
echo "TESTING WEBSITE SETTINGS CMS & STATE PERSISTENCE\n";
echo "========================================================\n\n";

$passed = 0;
$failed = 0;

function assertCond($name, $cond, $details = '') {
    global $passed, $failed;
    if ($cond) {
        echo " [PASS] $name\n";
        if ($details) echo "        $details\n";
        $passed++;
    } else {
        echo " [FAIL] $name\n";
        if ($details) echo "        $details\n";
        $failed++;
    }
}

// 1. Fetch current state
$ch = curl_init("http://127.0.0.1:8000/api/state");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$state = json_decode($res, true);
assertCond("State endpoint is reachable", $httpCode === 200 && is_array($state));
assertCond("Settings object exists in database state", isset($state['settings']) && is_array($state['settings']));

$settings = $state['settings'] ?? [];
assertCond("Hospital name exists in settings", !empty($settings['hospitalName']), "Hospital Name: " . ($settings['hospitalName'] ?? 'N/A'));
assertCond("Working hours exist in settings", isset($settings['workingHours']['weekdays']));
assertCond("Social media object exists in settings", isset($settings['socialMedia']['facebook']));

// 2. Test updating a non-destructive setting (e.g. defaultSeoTitle or tagline)
$updatedState = $state;
$testTitle = "Madhav Netralaya Eye Institute & Research Centre | Nagpur";
$updatedState['settings']['defaultSeoTitle'] = $testTitle;
$updatedState['settings']['defaultMetaDescription'] = "Madhav Netralaya Eye Institute & Research Centre, Nagpur is a leading tertiary eye hospital dedicated to providing world-class ophthalmic care.";

$ch = curl_init("http://127.0.0.1:8000/api/state");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updatedState));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$updateRes = curl_exec($ch);
$updateCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCond("POST /api/state returns HTTP 200 OK", $updateCode === 200);

// 3. Re-fetch and verify persistence
$ch = curl_init("http://127.0.0.1:8000/api/state");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$verifyRes = curl_exec($ch);
curl_close($ch);

$verifyState = json_decode($verifyRes, true);
assertCond("defaultSeoTitle persisted in database", ($verifyState['settings']['defaultSeoTitle'] ?? '') === $testTitle);
assertCond("Doctors count still intact", count($verifyState['doctors'] ?? []) === 18, "Doctors: " . count($verifyState['doctors'] ?? []));
assertCond("Custom pages count still intact", count($verifyState['customPages'] ?? []) === 53, "Pages: " . count($verifyState['customPages'] ?? []));

echo "\n========================================================\n";
echo "SUMMARY: $passed PASSED, $failed FAILED\n";
echo "========================================================\n";
