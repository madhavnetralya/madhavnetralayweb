<?php
/**
 * Automated Hard-Coded Secret & Credential Scanner
 * Scans application files in deployment/package without exposing secret values.
 */

$scanDir = __DIR__ . '/package';
$findings = [];

$extensions = ['php', 'js', 'ts', 'tsx', 'json', 'xml', 'yml', 'yaml', 'sql', 'md', 'env', 'example', 'ini', 'htaccess'];

$patterns = [
    'Hardcoded Admin Default Password' => [
        'regex' => '/[\'"]admin123[\'"]|password\s*===\s*[\'"][^\'"]+[\'"]/i',
        'type' => 'Authentication Credential'
    ],
    'Hardcoded API Key' => [
        'regex' => '/AIza[0-9A-Za-z-_]{35}|(?:api_key|apikey|api-key)\s*[:=]\s*[\'"][a-zA-Z0-9_\-]{20,}[\'"]/i',
        'type' => 'API Credential'
    ],
    'Instagram Access Token' => [
        'regex' => '/EAAB[0-9A-Za-z]+/i',
        'type' => 'Third-Party Token'
    ],
    'Razorpay Key / Secret' => [
        'regex' => '/rzp_(?:test|live)_[0-9a-zA-Z]{14}|RAZORPAY_KEY_SECRET\s*[:=]\s*[\'"][a-zA-Z0-9]{15,}[\'"]/i',
        'type' => 'Payment Credential'
    ],
    'SMTP Password / Secret' => [
        'regex' => '/MAIL_PASSWORD\s*=\s*(?!null|YOUR_|"")[^\s]+/i',
        'type' => 'Email Credential'
    ],
    'Bearer / JWT Token' => [
        'regex' => '/Bearer\s+eyJ[a-zA-Z0-9_\-\.]+/i',
        'type' => 'Authentication Token'
    ],
    'Private Key Block' => [
        'regex' => '/-----BEGIN (?:RSA )?PRIVATE KEY-----/i',
        'type' => 'Cryptographic Private Key'
    ],
    'Database Password in Connection' => [
        'regex' => '/DB_PASSWORD\s*=\s*(?!null|YOUR_|"")[^\s]+/i',
        'type' => 'Database Credential'
    ]
];

function scanDirectory($dir, $extensions, $patterns, &$findings, $baseDir) {
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || $item === '.git' || $item === 'vendor') {
            continue;
        }
        $fullPath = $dir . '/' . $item;
        if (is_dir($fullPath)) {
            scanDirectory($fullPath, $extensions, $patterns, $findings, $baseDir);
        } else {
            $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
            $filename = basename($fullPath);
            if (in_array($ext, $extensions) || strpos($filename, '.env') !== false) {
                $content = @file_get_contents($fullPath);
                if ($content === false) continue;

                $relPath = str_replace($baseDir . '/', '', $fullPath);

                foreach ($patterns as $name => $meta) {
                    if (preg_match_all($meta['regex'], $content, $matches, PREG_OFFSET_CAPTURE)) {
                        $matches_found = [];
                        foreach ($matches[0] as $match) {
                            $matches_found[] = substr($match[0], 0, 80);
                        }
                        $findings[] = [
                            'file' => $relPath,
                            'type' => $meta['type'],
                            'pattern' => $name,
                            'match_count' => count($matches[0]),
                            'sample' => implode(', ', array_unique($matches_found))
                        ];
                    }
                }
            }
        }
    }
}

scanDirectory($scanDir, $extensions, $patterns, $findings, $scanDir);

echo "=== SCAN RESULTS: " . count($findings) . " finding(s) ===\n\n";
foreach ($findings as $f) {
    echo "FILE: " . $f['file'] . "\n";
    echo "TYPE: " . $f['type'] . " (" . $f['pattern'] . ")\n";
    echo "STATUS: FOUND (" . $f['match_count'] . " instance(s)): " . $f['sample'] . "\n";
    echo "ACTION: Move to environment configuration / sanitize fallback\n";
    echo "--------------------------------------------------------\n";
}
