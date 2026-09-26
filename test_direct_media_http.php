<?php
/**
 * Direct Media HTTP Resolution Test
 * Tests serving actual media assets through Laravel's public web root and storage symlink.
 */

$testFiles = [
    'Doctor Photo 1' => 'cms/general/img_6a7972515fa75_1786344017.jpg',
    'Doctor Photo 2' => 'cms/general/file_6a9689f1ec9c5_1788250609.jpeg',
    'Event Image' => 'cms/general/file_6a968b067d798_1788250886.jpeg',
    'SVG Image' => 'images/school_eye_camp.svg',
];

$publicRoot = __DIR__ . '/package/public';

echo "=======================================================\n";
echo "LOCAL MEDIA DIRECT RESOLUTION TEST\n";
echo "=======================================================\n\n";

$allPassed = true;

foreach ($testFiles as $label => $relPath) {
    if (strpos($relPath, 'cms/') === 0) {
        $servedPath = $publicRoot . '/storage/' . $relPath;
        $urlPath = '/storage/' . $relPath;
    } else {
        $servedPath = $publicRoot . '/' . $relPath;
        $urlPath = '/' . $relPath;
    }

    echo "Testing [{$label}] -> {$urlPath}\n";
    if (file_exists($servedPath)) {
        $size = filesize($servedPath);
        $mime = mime_content_type($servedPath);
        echo "  STATUS: 200 OK | Size: {$size} bytes | MIME: {$mime}\n";
    } else {
        echo "  STATUS: 404 NOT FOUND (Path: {$servedPath})\n";
        $allPassed = false;
    }
    echo "-------------------------------------------------------\n";
}

// Test against running HTTP server on localhost if available
$urlsToTest = [
    'http://127.0.0.1:8000/storage/cms/general/img_6a7972515fa75_1786344017.jpg',
    'http://127.0.0.1:8000/images/school_eye_camp.svg'
];

echo "\nTesting against live server (http://127.0.0.1:8000):\n";
foreach ($urlsToTest as $url) {
    echo "GET {$url} ... ";
    $ctx = stream_context_create(['http' => ['timeout' => 2]]);
    $headers = @get_headers($url, 1, $ctx);
    if ($headers && strpos($headers[0], '200') !== false) {
        echo "SUCCESS (HTTP 200 OK, Content-Type: " . ($headers['Content-Type'] ?? 'N/A') . ")\n";
    } else {
        echo "Server not listening on 8000 or returned " . ($headers[0] ?? 'No Response') . "\n";
    }
}

echo "\n=======================================================\n";
if ($allPassed) {
    echo "ALL LOCAL MEDIA DIRECT ACCESS CHECKS PASSED!\n";
} else {
    echo "SOME MEDIA CHECKS FAILED!\n";
}
echo "=======================================================\n";
