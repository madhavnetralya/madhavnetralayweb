<?php

$dirs = [
    __DIR__ . '/package/public/assets',
    dirname(__DIR__) . '/laravel/public/assets'
];

$activeFiles = ['index-DThv2F_D.js', 'index-C1Gv5oUa.css'];
$removed = 0;

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        continue;
    }
    foreach (scandir($dir) as $file) {
        if (preg_match('/^index-.*\.(js|css)$/', $file)) {
            if (!in_array($file, $activeFiles)) {
                @unlink($dir . '/' . $file);
                echo "Cleaned: {$file} from " . basename(dirname($dir)) . "\n";
                $removed++;
            }
        }
    }
}

echo "Total stale assets removed: {$removed}\n";
