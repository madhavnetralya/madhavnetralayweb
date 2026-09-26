<?php
$dbPath = __DIR__ . '/package/database.db';
$storageBasePath = __DIR__ . '/package/public/storage';

$db = new SQLite3($dbPath);
$res = $db->query("SELECT value FROM state_store WHERE key='state'");
$row = $res->fetchArray(SQLITE3_ASSOC);
$state = json_decode($row['value'], true);

$totalChecked = 0;
$totalFound = 0;
$missing = [];

function checkMediaEntries($val, $keyPath, $storageBasePath, &$totalChecked, &$totalFound, &$missing) {
    if (is_array($val)) {
        foreach ($val as $k => $v) {
            checkMediaEntries($v, $keyPath ? "$keyPath.$k" : "$k", $storageBasePath, $totalChecked, $totalFound, $missing);
        }
    } elseif (is_string($val)) {
        $valTrim = trim($val);
        if (strpos($valTrim, 'cms/') === 0 || strpos($valTrim, '/storage/cms/') === 0 || strpos($valTrim, 'storage/cms/') === 0) {
            $totalChecked++;
            $cleanPath = preg_replace('/^\/?storage\//', '', $valTrim);
            $fullFilePath = $storageBasePath . '/' . $cleanPath;
            if (file_exists($fullFilePath)) {
                $totalFound++;
            } else {
                $missing[] = [
                    'key' => $keyPath,
                    'path' => $valTrim,
                    'resolved_file' => $fullFilePath
                ];
            }
        }
    }
}

checkMediaEntries($state, '', $storageBasePath, $totalChecked, $totalFound, $missing);

echo "=======================================================\n";
echo "DATABASE MEDIA INTEGRITY AUDIT (DEPLOYMENT PACKAGE)\n";
echo "=======================================================\n";
echo "Total CMS Media References in Database: {$totalChecked}\n";
echo "Successfully Resolved in public/storage: {$totalFound}\n";
echo "Missing Files: " . count($missing) . "\n";

if (count($missing) > 0) {
    echo "\nMissing Media Files Details:\n";
    foreach ($missing as $m) {
        echo "- Key: {$m['key']} | Path: {$m['path']}\n";
    }
} else {
    echo "\n100% of all database CMS media files exist and resolve properly!\n";
}
echo "=======================================================\n";
