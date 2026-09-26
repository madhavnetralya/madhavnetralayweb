<?php
$dbPath = __DIR__ . '/package/database.db';
$db = new SQLite3($dbPath);

$res = $db->query("SELECT value FROM state_store WHERE key='state'");
$row = $res->fetchArray(SQLITE3_ASSOC);

if (!$row) {
    echo "No state found.\n";
    exit(1);
}

$state = json_decode($row['value'], true);

$mediaStats = [
    'storage_cms_paths' => 0,
    'cms_relative_paths' => 0,
    'uploads_paths' => 0,
    'images_paths' => 0,
    'full_url_paths' => 0,
    'base64_paths' => 0,
    'samples' => []
];

function scanForMedia($val, $keyPath, &$stats) {
    if (is_array($val)) {
        foreach ($val as $k => $v) {
            scanForMedia($v, $keyPath ? "$keyPath.$k" : "$k", $stats);
        }
    } elseif (is_string($val)) {
        $valTrim = trim($val);
        if (preg_match('/\.(jpg|jpeg|png|webp|gif|svg|mp4|webm|pdf)/i', $valTrim) || strpos($valTrim, 'data:image') === 0 || strpos($valTrim, 'cms/') === 0 || strpos($valTrim, '/storage/') === 0) {
            if (strpos($valTrim, 'http://') === 0 || strpos($valTrim, 'https://') === 0) {
                $stats['full_url_paths']++;
                if (count($stats['samples']) < 20) $stats['samples'][] = "[$keyPath] Full URL: $valTrim";
            } elseif (strpos($valTrim, 'data:image') === 0) {
                $stats['base64_paths']++;
                if (count($stats['samples']) < 20) $stats['samples'][] = "[$keyPath] Base64 Image (len " . strlen($valTrim) . ")";
            } elseif (strpos($valTrim, '/storage/') === 0 || strpos($valTrim, 'storage/') === 0) {
                $stats['storage_cms_paths']++;
                if (count($stats['samples']) < 20) $stats['samples'][] = "[$keyPath] Storage path: $valTrim";
            } elseif (strpos($valTrim, 'cms/') === 0) {
                $stats['cms_relative_paths']++;
                if (count($stats['samples']) < 20) $stats['samples'][] = "[$keyPath] CMS relative path: $valTrim";
            } elseif (strpos($valTrim, 'uploads/') === 0 || strpos($valTrim, '/uploads/') === 0) {
                $stats['uploads_paths']++;
                if (count($stats['samples']) < 20) $stats['samples'][] = "[$keyPath] Uploads path: $valTrim";
            } elseif (strpos($valTrim, 'images/') === 0 || strpos($valTrim, '/images/') === 0) {
                $stats['images_paths']++;
                if (count($stats['samples']) < 20) $stats['samples'][] = "[$keyPath] Images path: $valTrim";
            }
        }
    }
}

scanForMedia($state, '', $mediaStats);

echo "Media Path Statistics in SQLite Database State:\n";
echo "- Paths starting with 'cms/' (CMS relative): " . $mediaStats['cms_relative_paths'] . "\n";
echo "- Paths starting with '/storage/' or 'storage/': " . $mediaStats['storage_cms_paths'] . "\n";
echo "- Paths starting with 'uploads/': " . $mediaStats['uploads_paths'] . "\n";
echo "- Paths starting with 'images/': " . $mediaStats['images_paths'] . "\n";
echo "- Full external URLs (http/https): " . $mediaStats['full_url_paths'] . "\n";
echo "- Base64 embedded images: " . $mediaStats['base64_paths'] . "\n";

echo "\nSamples found:\n";
foreach ($mediaStats['samples'] as $sample) {
    echo "  $sample\n";
}
