<?php
$lines = file(__DIR__ . '/../src/components/UserPortal.tsx');
foreach ($lines as $num => $line) {
    if (strpos($line, 'openFooterModal') !== false) {
        echo "Line " . ($num + 1) . ": " . trim($line) . "\n";
    }
}
